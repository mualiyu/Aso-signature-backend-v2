<?php

namespace Webkul\Sales\Listeners;

use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderMeasurement;

class OrderMeasurementListener
{
    /**
     * Capture customer measurements after order is created
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @return void
     */
    public function handle($order)
    {
        if ($order instanceof Order && $order->customer_id) {
            // Get customer's current measurements
            $measurements = $order->customer->measurements()->get();

            // Snapshot measurements to order (excluding 0.00 values)
            foreach ($measurements as $measurement) {
                // Skip measurements with value 0.00 or null
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
}

