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
        'customer_id',
        'profile_id',
        'name',
        'value',
        'unit',
        'notes',
        'measurement_type',
        'gender',
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

    /**
     * Get the profile that owns the measurement.
     */
    public function profile()
    {
        return $this->belongsTo(MeasurementProfile::class, 'profile_id');
    }
}
