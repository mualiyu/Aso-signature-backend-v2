<?php

namespace Webkul\Alteration\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\Alteration\DataGrids\PartnerDataGrid;
use Webkul\Alteration\Http\Requests\PartnerRequest;
use Webkul\Alteration\Repositories\AlterationFixTypeRepository;
use Webkul\Alteration\Repositories\AlterationPartnerRepository;

class PartnerController extends Controller
{
    /**
     * Fields an admin may set on a partner.
     */
    protected const FIELDS = [
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
        'is_active',
        'currency',
        'side_conditions',
        'payment_terms',
        'notes',
        'prices',
    ];

    public function __construct(
        protected AlterationPartnerRepository $partnerRepository,
        protected AlterationFixTypeRepository $fixTypeRepository
    ) {}

    /**
     * Partner tracker listing.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(PartnerDataGrid::class)->process();
        }

        return view('alteration::admin.partners.index');
    }

    public function create()
    {
        return view('alteration::admin.partners.create', [
            'fixTypes' => $this->fixTypeRepository->getActive(),
        ]);
    }

    public function store(PartnerRequest $request)
    {
        $this->partnerRepository->create($request->only(self::FIELDS));

        session()->flash('success', trans('alteration::app.admin.partners.create-success'));

        return redirect()->route('admin.alterations.partners.index');
    }

    public function edit(int $id)
    {
        return view('alteration::admin.partners.edit', [
            'partner'  => $this->partnerRepository->with('prices')->findOrFail($id),
            'fixTypes' => $this->fixTypeRepository->getActive(),
        ]);
    }

    public function update(PartnerRequest $request, int $id)
    {
        $this->partnerRepository->update($request->only(self::FIELDS), $id);

        session()->flash('success', trans('alteration::app.admin.partners.update-success'));

        return redirect()->route('admin.alterations.partners.index');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->partnerRepository->findOrFail($id);

        $this->partnerRepository->delete($id);

        return new JsonResponse([
            'message' => trans('alteration::app.admin.partners.delete-success'),
        ]);
    }

    /**
     * Bulk-move partners along the outreach pipeline.
     */
    public function massUpdate(MassUpdateRequest $request): JsonResponse
    {
        $request->validate([
            'value' => [Rule::in(config('alteration.outreach_statuses'))],
        ]);

        foreach ($request->input('indices') as $id) {
            $this->partnerRepository->update(['outreach_status' => $request->input('value')], $id);
        }

        return new JsonResponse([
            'message' => trans('alteration::app.admin.partners.mass-update-success'),
        ]);
    }
}
