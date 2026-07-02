<?php

namespace Webkul\Shipping\Services;

use Illuminate\Support\Facades\Log;
use Webkul\Sales\Models\Shipment;
use Webkul\Shipping\Concerns\InteractsWithDhl;

/**
 * Books and cancels DHL Express courier pickups via the MyDHL `/pickups` endpoints. A pickup is
 * always booked for an already-created shipment, so the parcel it describes is reused from the
 * shipment's items / order via DHLShipmentService::buildPickupPartyDetails().
 */
class DhlPickupService
{
    use InteractsWithDhl;

    /**
     * Default reason sent to MyDHL when an admin cancels a booked pickup.
     */
    protected const CANCEL_REASON = 'Pickup no longer required';

    public function __construct(protected DHLShipmentService $shipmentService) {}

    /**
     * Book a courier pickup for a shipment via MyDHL `POST /pickups`.
     *
     * @param  array{planned_date: string, close_time?: string, location?: string, location_type?: string, special_instructions?: string}  $data
     * @return array{success: bool, data?: array, error?: string, status?: int}
     */
    public function bookPickup(Shipment $shipment, array $data): array
    {
        try {
            $order = $shipment->order;

            if (! $order) {
                return ['success' => false, 'error' => 'Shipment is not linked to an order.'];
            }

            $party = $this->shipmentService->buildPickupPartyDetails(
                $order,
                $this->shipmentDataFromShipment($shipment)
            );

            $requestBody = [
                'plannedPickupDateAndTime' => DhlPlannedShippingDate::formatDateForApi($data['planned_date']),
                'closeTime'                => $data['close_time'] ?? $this->dhlConfig('pickup_close_time', '18:00'),
                'location'                 => $data['location'] ?? $this->dhlConfig('pickup_location', 'reception'),
                'locationType'             => $data['location_type'] ?? $this->dhlConfig('pickup_location_type', 'business'),
                'accounts'                 => $party['accounts'],
                'customerDetails'          => $party['customerDetails'],
                'shipmentDetails'          => $party['shipmentDetails'],
            ];

            if (! empty($data['special_instructions'])) {
                $requestBody['specialInstructions'] = [
                    ['value' => $data['special_instructions']],
                ];
            }

            Log::info('DHL Pickup Booking Request', [
                'shipment_id' => $shipment->id,
                'order_id'    => $order->id,
                'url'         => $this->dhlBaseUrl().'/pickups',
                'date'        => $requestBody['plannedPickupDateAndTime'],
            ]);

            $response = $this->dhlRequest(60)->post($this->dhlBaseUrl().'/pickups', $requestBody);

            if ($response->successful()) {
                $body = $response->json();

                $dispatch = $this->extractDispatchConfirmationNumber($body);

                Log::info('DHL Pickup Booked', [
                    'shipment_id' => $shipment->id,
                    'dispatch'    => $dispatch,
                ]);

                return [
                    'success' => true,
                    'data'    => [
                        'dispatch_confirmation_number' => $dispatch,
                        'ready_by_time'                => $body['readyByTime'] ?? null,
                        'next_pickup_date'             => $body['nextPickupDate'] ?? null,
                        'warnings'                     => $body['warnings'] ?? null,
                        'dhl_response'                 => $body,
                    ],
                ];
            }

            $error = $this->parseDhlError($response);

            Log::error('DHL Pickup Booking Error', [
                'shipment_id' => $shipment->id,
                'status'      => $response->status(),
                'error'       => $error,
                'body'        => $response->body(),
            ]);

            return ['success' => false, 'error' => $error, 'status' => $response->status()];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('DHL Pickup Network Error', [
                'shipment_id' => $shipment->id,
                'message'     => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => 'Network Error: Cannot connect to DHL API. Please check your connection and try again.',
            ];
        } catch (\Throwable $e) {
            Log::error('DHL Pickup Booking Exception', [
                'shipment_id' => $shipment->id,
                'message'     => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'API Error: '.$e->getMessage()];
        }
    }

    /**
     * Cancel a booked pickup via MyDHL `DELETE /pickups/{dispatchConfirmationNumber}`. A 404/410
     * means DHL has nothing left to cancel (already cancelled / never registered) and is treated
     * as success. A shipment with no confirmation number is a no-op.
     *
     * @param  array{requestor_name?: string, reason?: string}  $data
     * @return array{success: bool, skipped?: bool, error?: string}
     */
    public function cancelPickup(Shipment $shipment, array $data = []): array
    {
        $dispatch = $shipment->dhl_pickup_confirmation_number;

        if (empty($dispatch)) {
            return ['success' => true, 'skipped' => true];
        }

        try {
            $response = $this->dhlRequest(30)->delete($this->dhlBaseUrl().'/pickups/'.urlencode($dispatch), [
                'requestorName' => $data['requestor_name'] ?? ($this->dhlConfig('origin_company') ?: 'Shipper'),
                'reason'        => $data['reason'] ?? self::CANCEL_REASON,
            ]);

            if ($response->successful() || in_array($response->status(), [404, 410], true)) {
                Log::info('DHL Pickup Cancelled', [
                    'shipment_id' => $shipment->id,
                    'dispatch'    => $dispatch,
                    'status'      => $response->status(),
                ]);

                return ['success' => true];
            }

            $error = $this->parseDhlError($response);

            Log::error('DHL Pickup Cancellation Error', [
                'shipment_id' => $shipment->id,
                'status'      => $response->status(),
                'body'        => $response->body(),
            ]);

            return ['success' => false, 'error' => $error];
        } catch (\Throwable $e) {
            Log::error('DHL Pickup Cancellation Exception', [
                'shipment_id' => $shipment->id,
                'message'     => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Reconstruct the {source, items} shape that DHLShipmentService's builders expect from the
     * shipment's own items, so the pickup covers exactly what was shipped.
     *
     * @return array{source: int, items: array<int, array<int, float>>}
     */
    protected function shipmentDataFromShipment(Shipment $shipment): array
    {
        $sourceId = (int) ($shipment->inventory_source_id ?? 0);

        $items = [];

        foreach ($shipment->items as $shipmentItem) {
            if ($shipmentItem->order_item_id) {
                $items[$shipmentItem->order_item_id] = [$sourceId => (float) $shipmentItem->qty];
            }
        }

        return [
            'source' => $sourceId,
            'items'  => $items,
        ];
    }

    /**
     * MyDHL returns the dispatch reference as `dispatchConfirmationNumbers` (array); fall back to
     * the singular form used elsewhere in the API for resilience.
     */
    protected function extractDispatchConfirmationNumber(?array $body): ?string
    {
        if (empty($body)) {
            return null;
        }

        if (! empty($body['dispatchConfirmationNumbers']) && is_array($body['dispatchConfirmationNumbers'])) {
            return (string) $body['dispatchConfirmationNumbers'][0];
        }

        return $body['dispatchConfirmationNumber'] ?? null;
    }
}
