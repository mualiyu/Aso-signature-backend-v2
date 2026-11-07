<?php

namespace Webkul\Alteration\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Webkul\Alteration\Contracts\AlterationClaim;
use Webkul\Alteration\Models\AlterationClaim as AlterationClaimModel;
use Webkul\Alteration\Services\ClaimEligibility;
use Webkul\Core\Eloquent\Repository;
use Webkul\Sales\Models\Order;

class AlterationClaimRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return AlterationClaim::class;
    }

    /**
     * File a claim for an order: snapshot the confirmed measurements next to what the customer
     * measured on the garment, and store the photo evidence.
     *
     * @param  array{reason: string, description?: ?string, garment?: array<int|string, mixed>}  $data  `garment` is keyed by order_measurements id
     * @param  array<int, \Illuminate\Http\UploadedFile>  $photos
     */
    public function createForOrder(Order $order, array $data, array $photos): AlterationClaimModel
    {
        return DB::transaction(function () use ($order, $data, $photos) {
            /**
             * Serialise concurrent submissions for the same order, then re-check eligibility
             * inside the lock so a double-submit cannot open two claims.
             */
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            $eligibility = app(ClaimEligibility::class)->check($order);

            if (! $eligibility['eligible']) {
                throw ValidationException::withMessages([
                    'reason' => trans('alteration::app.shop.claims.errors.'.$eligibility['reason']),
                ]);
            }

            $claim = $this->model->create([
                'order_id'      => $order->id,
                'customer_id'   => $order->customer_id,
                'reason'        => $data['reason'],
                'description'   => $data['description'] ?? null,
                'delivered_at'  => $order->delivered_at,
                'status'        => AlterationClaimModel::STATUS_SUBMITTED,
                'currency'      => core()->getBaseCurrencyCode(),
                'payout_status' => AlterationClaimModel::PAYOUT_NOT_APPLICABLE,
            ]);

            $measurements = $order->measurements()->get()->keyBy('id');

            foreach ($data['garment'] ?? [] as $measurementId => $garmentValue) {
                $measurement = $measurements->get((int) $measurementId);

                if (! $measurement || $garmentValue === null || $garmentValue === '') {
                    continue;
                }

                $claim->items()->create([
                    'order_item_id'    => $measurement->order_item_id,
                    'measurement_name' => $measurement->name,
                    'confirmed_value'  => $measurement->value,
                    'garment_value'    => $garmentValue,
                    'difference'       => $measurement->value !== null
                        ? round((float) $garmentValue - (float) $measurement->value, 2)
                        : null,
                    'unit'             => $measurement->unit,
                ]);
            }

            foreach (array_values($photos) as $index => $photo) {
                $claim->images()->create([
                    'path' => $photo->store('alteration/'.$claim->id),
                    'sort' => $index,
                ]);
            }

            return $claim;
        });
    }

    /**
     * Another claim on the same order that already used the one-off ASO-covered alteration.
     */
    public function findOtherCoveredClaim(AlterationClaimModel $claim): ?AlterationClaimModel
    {
        return $this->model
            ->where('order_id', $claim->order_id)
            ->where('id', '!=', $claim->id)
            ->whereIn('status', AlterationClaimModel::ASO_COVERED_STATUSES)
            ->lockForUpdate()
            ->first();
    }
}
