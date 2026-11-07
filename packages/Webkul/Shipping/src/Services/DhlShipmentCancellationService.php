<?php

namespace Webkul\Shipping\Services;

use Webkul\Sales\Models\Shipment;

class DhlShipmentCancellationService
{
    public function __construct(
        protected DhlTrackingService $dhlTrackingService,
        protected DhlPickupService $dhlPickupService
    ) {}

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
     * MyDHL API has no label-void endpoint; the only cancellation operation is deleting the courier
     * pickup booked for the shipment. This delegates to DhlPickupService so shipment cancellation
     * and the standalone "Cancel Pickup" action share one code path against the documented
     * `DELETE /pickups/{dispatchConfirmationNumber}` endpoint. A shipment with no booked pickup is
     * a no-op — the waybill simply expires unbilled.
     *
     * @return array{success: bool, skipped?: bool, error?: string}
     */
    public function cancelAtDhl(Shipment $shipment): array
    {
        return $this->dhlPickupService->cancelPickup($shipment);
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
}
