<?php

namespace Webkul\Alteration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Alteration\Contracts\AlterationClaim as AlterationClaimContract;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Sales\Models\OrderProxy;

class AlterationClaim extends Model implements AlterationClaimContract
{
    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_INFO_REQUESTED = 'info_requested';

    public const STATUS_ASO_FAULT_APPROVED = 'aso_fault_approved';

    public const STATUS_CUSTOMER_FAULT = 'customer_fault';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PARTNER_ASSIGNED = 'partner_assigned';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CLOSED = 'closed';

    public const FAULT_ASO = 'aso';

    public const FAULT_CUSTOMER = 'customer';

    public const PAYOUT_NOT_APPLICABLE = 'not_applicable';

    public const PAYOUT_PENDING = 'pending';

    public const PAYOUT_PAID = 'paid';

    /**
     * Statuses meaning Aso Signature accepted fault and used the order's one-off alteration.
     */
    public const ASO_COVERED_STATUSES = [
        self::STATUS_ASO_FAULT_APPROVED,
        self::STATUS_PARTNER_ASSIGNED,
        self::STATUS_COMPLETED,
    ];

    protected $table = 'alteration_claims';

    protected $fillable = [
        'order_id',
        'customer_id',
        'reason',
        'description',
        'delivered_at',
        'status',
        'fault',
        'fault_notes',
        'classified_by_admin_id',
        'classified_at',
        'alteration_partner_id',
        'fix_type_ids',
        'approved_cap_amount',
        'quoted_cost',
        'cap_override',
        'cap_override_note',
        'actual_cost',
        'currency',
        'payout_status',
        'paid_at',
        'payout_reference',
        'admin_notes',
        'customer_message',
    ];

    protected $casts = [
        'delivered_at'  => 'datetime',
        'classified_at' => 'datetime',
        'paid_at'       => 'datetime',
        'fix_type_ids'  => 'array',
        'cap_override'  => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderProxy::modelClass());
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(AlterationPartnerProxy::modelClass(), 'alteration_partner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AlterationClaimItemProxy::modelClass(), 'alteration_claim_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(AlterationClaimImageProxy::modelClass(), 'alteration_claim_id')->orderBy('sort');
    }

    /**
     * Whether the claim is still being worked on (blocks a second claim on the order).
     */
    public function isOpen(): bool
    {
        return in_array($this->status, config('alteration.open_claim_statuses'), true);
    }
}
