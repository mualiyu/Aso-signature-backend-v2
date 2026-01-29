<?php

namespace Webkul\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Sales\Contracts\OrderMeasurement as OrderMeasurementContract;

class OrderMeasurement extends Model implements OrderMeasurementContract
{
    protected $table = 'order_measurements';

    protected $fillable = [
        'order_id',
        'customer_id',
        'name',
        'value',
        'unit',
        'notes',
        'measurement_type',
    ];

    /**
     * Get the order that owns the measurement.
     */
    public function order()
    {
        return $this->belongsTo(OrderProxy::modelClass());
    }

    /**
     * Get the customer that owns the measurement.
     */
    public function customer()
    {
        return $this->belongsTo(\Webkul\Customer\Models\CustomerProxy::modelClass());
    }
}

