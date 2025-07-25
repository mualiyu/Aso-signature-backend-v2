<?php

namespace Webkul\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Contracts\Measurement as MeasurementContract;

class Measurement extends Model implements MeasurementContract
{

    protected $table = 'measurements';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'customer_id',   // ID of the customer this measurement belongs to
        'name',          // Name of the measurement (e.g., 'chest', 'waist')
        'value',         // Numeric value of the measurement
        'unit',          // Unit of measurement (e.g., 'cm', 'inches')
        'notes',         // Additional notes about the measurement
        'measurement_type' // Type of measurement category
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the customer that owns the measurement.
     */
    public function customer()
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }
}
