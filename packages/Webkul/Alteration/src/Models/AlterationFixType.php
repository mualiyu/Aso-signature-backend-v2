<?php

namespace Webkul\Alteration\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Alteration\Contracts\AlterationFixType as AlterationFixTypeContract;

class AlterationFixType extends Model implements AlterationFixTypeContract
{
    protected $table = 'alteration_fix_types';

    protected $fillable = [
        'code',
        'name',
        'description',
        'aso_cap_amount',
        'currency',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
