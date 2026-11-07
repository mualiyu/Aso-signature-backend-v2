<?php

namespace Webkul\Alteration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Alteration\Contracts\AlterationClaimItem as AlterationClaimItemContract;
use Webkul\Sales\Models\OrderItemProxy;

class AlterationClaimItem extends Model implements AlterationClaimItemContract
{
    protected $table = 'alteration_claim_items';

    protected $fillable = [
        'alteration_claim_id',
        'order_item_id',
        'measurement_name',
        'confirmed_value',
        'garment_value',
        'difference',
        'unit',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(AlterationClaimProxy::modelClass(), 'alteration_claim_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItemProxy::modelClass(), 'order_item_id');
    }

    /**
     * Whether the difference exceeds the configured review tolerance for its unit.
     */
    public function exceedsTolerance(): bool
    {
        if ($this->difference === null) {
            return false;
        }

        $tolerance = config('alteration.tolerance.'.($this->unit ?: 'cm'), config('alteration.tolerance.cm'));

        return abs((float) $this->difference) > $tolerance;
    }
}
