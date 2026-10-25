<?php

namespace Webkul\Alteration\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Alteration\Repositories\AlterationFixTypeRepository;

class FixTypeController extends Controller
{
    public function __construct(protected AlterationFixTypeRepository $fixTypeRepository) {}

    /**
     * The ASO cost-cap price sheet: one editable row per fix type.
     */
    public function index()
    {
        return view('alteration::admin.fix-types.index', [
            'fixTypes' => $this->fixTypeRepository->orderBy('sort')->get(),
        ]);
    }

    /**
     * Save every row of the price sheet, and add a new fix type when one is filled in.
     */
    public function update()
    {
        $data = request()->validate([
            'fix_types'                  => ['nullable', 'array'],
            'fix_types.*.name'           => ['required', 'string', 'max:255'],
            'fix_types.*.aso_cap_amount' => ['nullable', 'numeric', 'min:0'],
            'fix_types.*.currency'       => ['nullable', 'string', 'size:3'],
            'fix_types.*.sort'           => ['nullable', 'integer', 'min:0'],
            'fix_types.*.is_active'      => ['nullable', 'boolean'],
            'new.name'                   => ['nullable', 'string', 'max:255'],
            'new.aso_cap_amount'         => ['nullable', 'numeric', 'min:0'],
            'new.currency'               => ['nullable', 'string', 'size:3'],
        ]);

        foreach ($data['fix_types'] ?? [] as $id => $row) {
            $this->fixTypeRepository->update([
                'name'           => $row['name'],
                'aso_cap_amount' => ($row['aso_cap_amount'] ?? '') !== '' ? $row['aso_cap_amount'] : null,
                'currency'       => ! empty($row['currency']) ? strtoupper($row['currency']) : null,
                'sort'           => (int) ($row['sort'] ?? 0),
                'is_active'      => (bool) ($row['is_active'] ?? false),
            ], $id);
        }

        if (! empty($data['new']['name'])) {
            $code = Str::slug($data['new']['name'], '_');

            if ($this->fixTypeRepository->findOneByField('code', $code)) {
                $code .= '_'.Str::lower(Str::random(4));
            }

            $this->fixTypeRepository->create([
                'code'           => $code,
                'name'           => $data['new']['name'],
                'aso_cap_amount' => ($data['new']['aso_cap_amount'] ?? '') !== '' ? $data['new']['aso_cap_amount'] : null,
                'currency'       => ! empty($data['new']['currency']) ? strtoupper($data['new']['currency']) : null,
                'sort'           => (int) $this->fixTypeRepository->max('sort') + 1,
                'is_active'      => true,
            ]);
        }

        session()->flash('success', trans('alteration::app.admin.fix-types.update-success'));

        return redirect()->route('admin.alterations.fix_types.index');
    }
}
