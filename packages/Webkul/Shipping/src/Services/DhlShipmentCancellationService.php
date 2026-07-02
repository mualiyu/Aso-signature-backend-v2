<?php

namespace Webkul\Shipping\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Sales\Models\Shipment;

class DhlShipmentCancellationService
{
    /**
     * MyDHL cancel reason code 006 = "Shipment cancelled by customer".
     */
    protected const CANCEL_REASON_CODE = '006';

    public function __construct(protected DhlTrackingService $dhlTrackingService) {}

    /**
     * A shipment can only be cancelled while DHL has not scanned the parcel into their network.
     * Unused waybills expire unbilled; once picked up, cancellation requires contacting DHL.
     *
     * @return array{cancellable: bool, reason?: string}
     */
    public function checkCancellable(Shipment $shipment): array
    {
        if (! empty($shipment->dhl_last_checkpoint_code)) {
            return [
                'cancellable' => false,
                'reason'      => trans('admin::app.sales.shipments.view.cancel-blocked-in-transit', [
                    'status' => $shipment->dhl_last_checkpoint_description ?: $shipment->dhl_last_checkpoint_code,
                ]),
            ];
        }

        if ($this->hasRefundedItems($shipment)) {
            return [
                'cancellable' => false,
                'reason'      => trans('admin::app.sales.shipments.view.cancel-blocked-refunded'),
            ];
        }

        if (empty($shipment->track_number)) {
            return ['cancellable' => true];
        }

        $tracking = $this->dhlTrackingService->fetchTracking($shipment->track_number);

        if ($tracking['success']) {
            if (! empty($tracking['checkpoints'])) {
                $latest = $tracking['latest'] ?? null;

                return [
                    'cancellable' => false,
                    'reason'      => trans('admin::app.sales.shipments.view.cancel-blocked-in-transit', [
                        'status' => $latest['description'] ?? 'In transit',
                    ]),
                ];
            }

            return ['cancellable' => true];
        }

        // 404 means the waybill was never scanned — the normal, safe-to-cancel case.
        if (($tracking['status'] ?? null) === 404) {
            return ['cancellable' => true];
        }

        return [
            'cancellable' => false,
            'reason'      => trans('admin::app.sales.shipments.view.cancel-tracking-unverified', [
                'message' => $tracking['error'] ?? 'Unknown error',
            ]),
        ];
    }

    /**
     * MyDHL API has no label-void endpoint; the only cancellation operation is deleting the
     * courier pickup booked at creation. Shipments created with pickup.isRequested=false have
     * nothing to cancel at DHL — the waybill simply expires unbilled.
     *
     * @return array{success: bool, skipped?: bool, error?: string}
     */
    public function cancelAtDhl(Shipment $shipment): array
    {
        if (empty($shipment->dhl_pickup_confirmation_number)) {
            return ['success' => true, 'skipped' => true];
        }

        try {
            $apiKey = $this->dhlConfig('api_key');
            $apiSecret = $this->dhlConfig('api_secret');

            if (empty($apiKey) || empty($apiSecret)) {
                throw new \Exception('DHL API credentials are not configured');
            }

            $baseUrl = $this->dhlConfig('sandbox_mode')
                ? 'https://express.api.dhl.com/mydhlapi/test'
                : 'https://express.api.dhl.com/mydhlapi';

            $requestorName = $this->dhlConfig('origin_company') ?: 'Shipper';

            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept'                 => 'application/json',
                    'Message-Reference'      => uniqid('', true),
                    'Message-Reference-Date' => now()->format('Y-m-d\TH:i:s\Z'),
                ])
                ->withBasicAuth($apiKey, $apiSecret)
                ->delete($baseUrl.'/shipments/'.urlencode($shipment->track_number).'/pickup', [
                    'dispatchConfirmationNumber' => $shipment->dhl_pickup_confirmation_number,
                    'requestorName'              => $requestorName,
                    'reason'                     => self::CANCEL_REASON_CODE,
                ]);

            // 404/410: nothing left to cancel at DHL — treat as success.
            if ($response->successful() || in_array($response->status(), [404, 410], true)) {
                Log::info('DHL pickup cancelled', [
                    'shipment_id' => $shipment->id,
                    'tracking'    => $shipment->track_number,
                    'dispatch'    => $shipment->dhl_pickup_confirmation_number,
                    'status'      => $response->status(),
                ]);

                return ['success' => true];
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['detail'] ?? $errorBody['message'] ?? 'Unable to cancel DHL pickup';

            Log::error('DHL pickup cancellation failed', [
                'shipment_id' => $shipment->id,
                'tracking'    => $shipment->track_number,
                'status'      => $response->status(),
                'body'        => $response->body(),
            ]);

            return ['success' => false, 'error' => $errorMessage];
        } catch (\Exception $e) {
            Log::error('DHL pickup cancellation error', [
                'shipment_id' => $shipment->id,
                'tracking'    => $shipment->track_number,
                'message'     => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Refunded items were already restocked by the refund — cancelling would double-restock.
     */
    protected function hasRefundedItems(Shipment $shipment): bool
    {
        foreach ($shipment->items as $shipmentItem) {
            $orderItem = $shipmentItem->order_item;

            if (! $orderItem) {
                continue;
            }

            if ((float) $orderItem->qty_refunded > 0) {
                return true;
            }

            foreach ($orderItem->children as $child) {
                if ((float) $child->qty_refunded > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function dhlConfig(string $key, $default = null)
    {
        return core()->getConfigData('sales.carriers.dhl.'.$key) ?? $default;
    }
}
