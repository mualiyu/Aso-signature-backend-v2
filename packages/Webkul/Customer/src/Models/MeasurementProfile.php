<?php

namespace Webkul\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Contracts\MeasurementProfile as MeasurementProfileContract;

class MeasurementProfile extends Model implements MeasurementProfileContract
{
    protected $table = 'measurement_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'customer_id',
        'name',
        'gender',
        'unit',
        'fit_preference',
        'fit_notes',
        'fit_notes_other',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fit_notes'  => 'array',
        'is_default' => 'boolean',
    ];

    /**
     * Get the customer that owns the profile.
     */
    public function customer()
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }

    /**
     * Get the measurements saved under this profile.
     */
    public function measurements()
    {
        return $this->hasMany(Measurement::class, 'profile_id');
    }
}
