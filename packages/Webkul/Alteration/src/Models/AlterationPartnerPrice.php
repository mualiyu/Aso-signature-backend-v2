<?php

namespace Webkul\Alteration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Alteration\Contracts\AlterationPartnerPrice as AlterationPartnerPriceContract;

class AlterationPartnerPrice extends Model implements AlterationPartnerPriceContract
{
    protected $table = 'alteration_partner_prices';

    protected $fillable = [
        'alteration_partner_id',
        'alteration_fix_type_id',
        'price',
        'turnaround_days',
        'notes',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(AlterationPartnerProxy::modelClass(), 'alteration_partner_id');
    }

    public function fixType(): BelongsTo
    {
        return $this->belongsTo(AlterationFixTypeProxy::modelClass(), 'alteration_fix_type_id');
    }
}
