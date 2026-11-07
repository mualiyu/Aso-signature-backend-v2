<?php

namespace Webkul\Alteration\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Alteration\DataGrids\ClaimDataGrid;
use Webkul\Alteration\Mail\CustomerClaimNotification;
use Webkul\Alteration\Models\AlterationClaim;
use Webkul\Alteration\Models\AlterationFixType;
use Webkul\Alteration\Models\AlterationPartner;
use Webkul\Alteration\Repositories\AlterationClaimRepository;
use Webkul\Alteration\Repositories\AlterationFixTypeRepository;
use Webkul\Alteration\Services\PartnerLocator;
use Webkul\Customer\Data\MeasurementFields;

class ClaimController extends Controller
{
    public function __construct(
        protected AlterationClaimRepository $claimRepository,
        protected AlterationFixTypeRepository $fixTypeRepository,
        protected PartnerLocator $partnerLocator
    ) {}

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ClaimDataGrid::class)->process();
        }

        return view('alteration::admin.claims.index');
    }

    /**
     * Review screen: evidence, comparison against the checkout snapshot, and the decision form.
     */
    public function view(int $id)
    {
        $claim = $this->claimRepository
            ->with(['order.items', 'items.orderItem', 'images', 'partner'])
            ->findOrFail($id);

        $address = $claim->order->shipping_address;

        $nearby = $address
            ? $this->partnerLocator->search((string) $address->country, $address->city, 50)
            : collect();

        $partners = $nearby->concat(
            AlterationPartner::query()
                ->listable()
                ->whereNotIn('id', $nearby->pluck('id'))
                ->orderBy('country')
                ->orderBy('city')
                ->get()
        );

        $snapshot = $claim->order->measurements()->get();

        return view('alteration::admin.claims.view', [
            'claim'        => $claim,
            'partners'     => $partners,
            'fixTypes'     => $this->fixTypeRepository->getActive(),
            'labels'       => MeasurementFields::labelMap(),
            'fitByItem'    => $snapshot->groupBy('order_item_id')->map(fn ($rows) => $rows->first()),
            'coveredClaim' => $this->claimRepository->findOtherCoveredClaim($claim),
        ]);
    }

    /**
     * Apply a decision. Fault is derived from the status, and the one-off ASO coverage, partner
     * vetting and cost-cap rules are enforced here.
     */
    public function update(int $id)
    {
        $data = request()->validate([
            'status'                => ['required', Rule::in(config('alteration.claim_statuses'))],
            'fault_notes'           => ['nullable', 'string', 'max:5000'],
            'customer_message'      => ['nullable', 'string', 'max:5000'],
            'alteration_partner_id' => ['nullable', 'integer', 'exists:alteration_partners,id'],
            'fix_type_ids'          => ['nullable', 'array'],
            'fix_type_ids.*'        => ['integer', 'exists:alteration_fix_types,id'],
            'quoted_cost'           => ['nullable', 'numeric', 'min:0'],
            'cap_override'          => ['nullable', 'boolean'],
            'cap_override_note'     => ['nullable', 'string', 'max:2000'],
            'actual_cost'           => ['nullable', 'numeric', 'min:0'],
            'payout_status'         => ['required', Rule::in(['not_applicable', 'pending', 'paid'])],
            'paid_at'               => ['nullable', 'date'],
            'payout_reference'      => ['nullable', 'string', 'max:255'],
            'admin_notes'           => ['nullable', 'string', 'max:5000'],
        ]);

        [$claim, $statusChanged] = DB::transaction(function () use ($id, $data) {
            $claim = $this->claimRepository->findOrFail($id);

            $previousStatus = $claim->status;

            $status = $data['status'];

            $fault = match ($status) {
                AlterationClaim::STATUS_ASO_FAULT_APPROVED => AlterationClaim::FAULT_ASO,
                AlterationClaim::STATUS_CUSTOMER_FAULT     => AlterationClaim::FAULT_CUSTOMER,
                AlterationClaim::STATUS_SUBMITTED,
                AlterationClaim::STATUS_UNDER_REVIEW,
                AlterationClaim::STATUS_INFO_REQUESTED,
                AlterationClaim::STATUS_REJECTED           => null,
                default                                    => $claim->fault,
            };

            $errors = [];

            $asoCovered = in_array($status, AlterationClaim::ASO_COVERED_STATUSES, true);

            if ($asoCovered && $fault !== AlterationClaim::FAULT_ASO) {
                $errors['status'] = trans('alteration::app.admin.claims.fault-required');
            }

            if ($asoCovered && blank($data['fault_notes'] ?? null)) {
                $errors['fault_notes'] = trans('alteration::app.admin.claims.aso-needs-notes');
            }

            /**
             * One-off limit: only one claim per order may use ASO coverage. Locked so two admins
             * approving different claims on the same order at once cannot both succeed.
             */
            if ($asoCovered && ($other = $this->claimRepository->findOtherCoveredClaim($claim))) {
                $errors['status'] = trans('alteration::app.admin.claims.one-off-used', ['id' => $other->id]);
            }

            $partnerId = $data['alteration_partner_id'] ?? null;

            if ($partnerId && ! AlterationPartner::query()->listable()->whereKey($partnerId)->exists()
                && (int) $partnerId !== (int) $claim->alteration_partner_id) {
                $errors['alteration_partner_id'] = trans('alteration::app.admin.claims.partner-not-listable');
            }

            if (in_array($status, [AlterationClaim::STATUS_PARTNER_ASSIGNED, AlterationClaim::STATUS_COMPLETED], true) && ! $partnerId) {
                $errors['alteration_partner_id'] = trans('alteration::app.admin.claims.partner-required');
            }

            $fixTypeIds = array_values(array_map('intval', $data['fix_type_ids'] ?? []));

            $cap = $this->capFor($fixTypeIds);

            $quote = $data['quoted_cost'] ?? null;

            $override = (bool) ($data['cap_override'] ?? false);

            if ($quote !== null && $quote !== '' && ($cap === null || (float) $quote > $cap)
                && (! $override || blank($data['cap_override_note'] ?? null))) {
                $errors['quoted_cost'] = trans('alteration::app.admin.claims.quote-above-cap');
            }

            if ($errors) {
                throw ValidationException::withMessages($errors);
            }

            $payoutStatus = $data['payout_status'];

            if ($fault === AlterationClaim::FAULT_ASO && $partnerId && $payoutStatus === AlterationClaim::PAYOUT_NOT_APPLICABLE) {
                $payoutStatus = AlterationClaim::PAYOUT_PENDING;
            }

            $claim->update([
                'status'                 => $status,
                'fault'                  => $fault,
                'fault_notes'            => $data['fault_notes'] ?? null,
                'classified_by_admin_id' => $fault !== $claim->fault ? auth()->guard('admin')->id() : $claim->classified_by_admin_id,
                'classified_at'          => $fault !== $claim->fault ? ($fault ? now() : null) : $claim->classified_at,
                'customer_message'       => $data['customer_message'] ?? null,
                'alteration_partner_id'  => $partnerId,
                'fix_type_ids'           => $fixTypeIds,
                'approved_cap_amount'    => $cap,
                'quoted_cost'            => $quote,
                'cap_override'           => $override,
                'cap_override_note'      => $override ? ($data['cap_override_note'] ?? null) : null,
                'actual_cost'            => $data['actual_cost'] ?? null,
                'payout_status'          => $payoutStatus,
                'paid_at'                => $payoutStatus === AlterationClaim::PAYOUT_PAID
                    ? ($data['paid_at'] ?? $claim->paid_at ?? now())
                    : null,
                'payout_reference'      => $data['payout_reference'] ?? null,
                'admin_notes'           => $data['admin_notes'] ?? null,
            ]);

            return [$claim, $previousStatus !== $status];
        });

        if ($statusChanged) {
            try {
                Mail::queue(new CustomerClaimNotification($claim->load(['order', 'partner'])));
            } catch (\Exception $e) {
                report($e);
            }
        }

        session()->flash('success', trans('alteration::app.admin.claims.update-success'));

        return redirect()->route('admin.alterations.claims.view', $claim->id);
    }

    /**
     * Sum of the ASO caps for the chosen fix types. Null when any of them has no cap set yet, so
     * every quote then needs an explicit override.
     *
     * @param  array<int, int>  $fixTypeIds
     */
    protected function capFor(array $fixTypeIds): ?float
    {
        if (empty($fixTypeIds)) {
            return null;
        }

        $caps = AlterationFixType::query()->whereIn('id', $fixTypeIds)->pluck('aso_cap_amount');

        if ($caps->contains(null)) {
            return null;
        }

        return (float) $caps->sum();
    }
}
