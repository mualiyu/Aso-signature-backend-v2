<?php

namespace Webkul\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Sales\Contracts\OrderMeasurement as OrderMeasurementContract;

class OrderMeasurement extends Model implements OrderMeasurementContract
{
    protected $table = 'order_measurements';

    protected $fillable = [
        'order_id',
        'order_item_id',
        'customer_id',
        'profile_name',
        'name',
        'value',
        'unit',
        'notes',
        'measurement_type',
        'fit_preference',
        'fit_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fit_notes' => 'array',
    ];

    /**
     * Get the order that owns the measurement.
     */
    public function order()
    {
        return $this->belongsTo(OrderProxy::modelClass());
    }

    /**
     * Get the order item that owns the measurement.
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItemProxy::modelClass(), 'order_item_id');
    }

    /**
     * Get the customer that owns the measurement.
     */
    public function customer()
    {
        return $this->belongsTo(\Webkul\Customer\Models\CustomerProxy::modelClass());
    }
}

