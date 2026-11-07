<?php

namespace Webkul\Alteration\Repositories;

use Illuminate\Support\Facades\DB;
use Webkul\Alteration\Contracts\AlterationPartner;
use Webkul\Alteration\Models\AlterationPartner as AlterationPartnerModel;
use Webkul\Core\Eloquent\Repository;

class AlterationPartnerRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return AlterationPartner::class;
    }

    /**
     * Create a partner with its price sheet.
     *
     * @return \Webkul\Alteration\Contracts\AlterationPartner
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $partner = parent::create($this->prepareVetting($data));

            $this->syncPrices($partner, $data['prices'] ?? []);

            return $partner;
        });
    }

    /**
     * Update a partner and its price sheet.
     *
     * @param  int  $id
     * @return \Webkul\Alteration\Contracts\AlterationPartner
     */
    public function update(array $data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $partner = $this->findOrFail($id);

            $partner->update($this->prepareVetting($data, $partner));

            if (array_key_exists('prices', $data)) {
                $this->syncPrices($partner, $data['prices'] ?? []);
            }

            return $partner;
        });
    }

    /**
     * Stamp who vetted the partner and when, the first time it becomes vetted. A partner that is
     * not vetted can never be live on the storefront.
     */
    protected function prepareVetting(array $data, $partner = null): array
    {
        $status = $data['vetting_status'] ?? $partner?->vetting_status;

        if ($status === AlterationPartnerModel::VETTING_VETTED) {
            if (! $partner || $partner->vetting_status !== AlterationPartnerModel::VETTING_VETTED) {
                $data['vetted_at'] = now();
                $data['vetted_by_admin_id'] = auth()->guard('admin')->id();
            }
        } elseif (array_key_exists('vetting_status', $data)) {
            $data['vetted_at'] = null;
            $data['vetted_by_admin_id'] = null;
            $data['is_active'] = false;
        }

        return $data;
    }

    /**
     * Replace the partner's price sheet. Rows without a price are removed.
     *
     * @param  array<int|string, array{price?: mixed, turnaround_days?: mixed, notes?: mixed}>  $prices  keyed by fix type id
     */
    protected function syncPrices($partner, array $prices): void
    {
        $keep = [];

        foreach ($prices as $fixTypeId => $row) {
            if (! isset($row['price']) || $row['price'] === '') {
                continue;
            }

            $partner->prices()->updateOrCreate(
                ['alteration_fix_type_id' => (int) $fixTypeId],
                [
                    'price'           => $row['price'],
                    'turnaround_days' => ($row['turnaround_days'] ?? '') !== '' ? (int) $row['turnaround_days'] : null,
                    'notes'           => $row['notes'] ?? null,
                ]
            );

            $keep[] = (int) $fixTypeId;
        }

        $partner->prices()->whereNotIn('alteration_fix_type_id', $keep)->delete();
    }
}
