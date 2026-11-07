<?php

namespace Webkul\Alteration\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Alteration\Contracts\AlterationPartner as AlterationPartnerContract;

class AlterationPartner extends Model implements AlterationPartnerContract
{
    public const VETTING_PENDING = 'pending';

    public const VETTING_VETTED = 'vetted';

    public const VETTING_REJECTED = 'rejected';

    protected $table = 'alteration_partners';

    protected $fillable = [
        'name',
        'contact_name',
        'email',
        'phone',
        'whatsapp',
        'website',
        'address',
        'city',
        'country',
        'latitude',
        'longitude',
        'outreach_status',
        'sourced_by',
        'vetting_status',
        'vetting_checklist',
        'vetted_at',
        'vetted_by_admin_id',
        'is_active',
        'currency',
        'side_conditions',
        'payment_terms',
        'notes',
    ];

    protected $casts = [
        'vetting_checklist' => 'array',
        'vetted_at'         => 'datetime',
        'is_active'         => 'boolean',
        'latitude'          => 'float',
        'longitude'         => 'float',
    ];

    /**
     * Prices this partner charges per fix type.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(AlterationPartnerPriceProxy::modelClass(), 'alteration_partner_id');
    }

    /**
     * Partners customers may be referred to: vetted and switched on.
     */
    public function scopeListable(Builder $query): Builder
    {
        return $query
            ->where('vetting_status', self::VETTING_VETTED)
            ->where('is_active', true);
    }

    /**
     * Whether every item on the vetting checklist has been ticked.
     */
    public function isChecklistComplete(): bool
    {
        return self::checklistIsComplete($this->vetting_checklist ?? []);
    }

    /**
     * @param  array<int, string>  $ticked
     */
    public static function checklistIsComplete(array $ticked): bool
    {
        return empty(array_diff(config('alteration.vetting_checklist'), $ticked));
    }
}
