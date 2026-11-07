<?php

namespace Webkul\Sales\Listeners;

use Webkul\Customer\Models\MeasurementProfile;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderMeasurement;

class OrderMeasurementListener
{
    /**
     * Capture customer measurements after order is created.
     *
     * Each order item gets a snapshot of its assigned measurement profile
     * (falling back to the customer's default profile), including the profile
     * name and fit data.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @return void
     */
    public function handle($order)
    {
        if (! $order instanceof Order || ! $order->customer_id) {
            return;
        }

        $profiles = MeasurementProfile::query()
            ->where('customer_id', $order->customer_id)
            ->with('measurements')
            ->get()
            ->keyBy('id');

        $defaultProfile = $profiles->firstWhere('is_default', true) ?: $profiles->first();

        if ($profiles->isEmpty()) {
            // Legacy fallback: customer has flat measurements without profiles.
            $this->snapshotLegacyMeasurements($order);

            return;
        }

        foreach ($order->items as $item) {
            $profileId = (int) data_get($item->additional, 'measurement_profile_id');

            $profile = ($profileId ? $profiles->get($profileId) : null) ?: $defaultProfile;

            if ($profile) {
                $this->snapshotProfile($order, $item, $profile);
            }
        }
    }

    /**
     * Snapshot a profile's measurements against an order item.
     */
    protected function snapshotProfile(Order $order, OrderItem $item, MeasurementProfile $profile): void
    {
        $fitNotes = collect($profile->fit_notes ?: [])
            ->reject(fn ($note) => $note === 'other')
            ->values()
            ->all();

        if ($profile->fit_notes_other) {
            $fitNotes[] = $profile->fit_notes_other;
        }

        foreach ($profile->measurements as $measurement) {
            if (empty($measurement->value) || (float) $measurement->value == 0.00) {
                continue;
            }

            OrderMeasurement::create([
                'order_id'          => $order->id,
                'order_item_id'     => $item->id,
                'customer_id'       => $order->customer_id,
                'profile_name'      => $profile->name,
                'name'              => $measurement->name,
                'value'             => $measurement->value,
                'unit'              => $measurement->unit,
                'notes'             => $measurement->notes,
                'measurement_type'  => $measurement->measurement_type,
                'fit_preference'    => $profile->fit_preference,
                'fit_notes'         => $fitNotes ?: null,
            ]);
        }
    }

    /**
     * Order-level snapshot of flat customer measurements (pre-profile data).
     */
    protected function snapshotLegacyMeasurements(Order $order): void
    {
        $measurements = $order->customer->measurements()->get();

        foreach ($measurements as $measurement) {
            if (empty($measurement->value) || (float) $measurement->value == 0.00) {
                continue;
            }

            OrderMeasurement::create([
                'order_id'          => $order->id,
                'customer_id'       => $order->customer_id,
                'name'              => $measurement->name,
                'value'             => $measurement->value,
                'unit'              => $measurement->unit,
                'notes'             => $measurement->notes,
                'measurement_type'  => $measurement->measurement_type,
            ]);
        }
    }
}
