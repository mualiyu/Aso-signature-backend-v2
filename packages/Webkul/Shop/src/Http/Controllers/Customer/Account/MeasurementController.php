<?php

namespace Webkul\Shop\Http\Controllers\Customer\Account;

use Webkul\Customer\Repositories\CustomerMeasurementRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
// use Webkul\Customer\Repositories\MeasurementRepository;
use Webkul\Shop\Http\Controllers\Controller;
use Webkul\Shop\Http\Requests\Customer\MeasurementRequest;

class MeasurementController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct(
    //     protected CustomerMeasurementRepository $customerMeasurementRepository
    // ) {}

    /**
     * Address route index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $measurements = auth()->guard('customer')->user()->measurements;

        return view('shop::customers.account.measurements.create')
            ->with('measurements', $measurements);
    }

    /**
     * Show the address create form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('shop::customers.account.measurements.create');
    }

    /**
     * Create a new address for customer.
     *
     * @return view
     */
    public function store(MeasurementRequest $request)
    {
        $customer = auth()->guard('customer')->user();

        // return $request;

        Event::dispatch('customer.measurements.create.before');

        $validated = $request;
        $unit = $validated['unit'] ?? 'Inches';
        $measurements = $validated['measurements'] ?? [];

        // Prepare all measurements as flat array
        // return $validated['measurements'] ?? [];

        // Standard categories
        foreach (['top', 'skirt', 'dress', 'trouser'] as $type) {
            if (!empty($measurements[$type]) && is_array($measurements[$type])) {
                foreach ($measurements[$type] as $name => $value) {
                    if ($value !== null && $value !== '') {
                        $allMeasurements[] = [
                            'customer_id'      => $customer->id,
                            'name'             => $name,
                            'value'            => $value['value'] ?? null,
                            'unit'             => $unit,
                            'measurement_type' => $type,
                            'notes'            => null,
                        ];
                    }
                }
            }
        }

        // Custom measurements
        if (!empty($measurements['custom']) && is_array($measurements['custom'])) {
            foreach ($measurements['custom'] as $custom) {
                if (!empty($custom['name']) && isset($custom['value'])) {
                    $allMeasurements[] = [
                        'customer_id'      => $customer->id,
                        'name'             => \Str::slug($custom['name'], '_'),
                        'value'            => $custom['value'] ?? null,
                        'unit'             => $unit,
                        'measurement_type' => 'custom',
                        'notes'            => null,
                    ];
                }
            }
        }

        // return $allMeasurements;

        // Store or update each measurement
        foreach ($allMeasurements as $data) {
            \Webkul\Customer\Models\Measurement::updateOrCreate(
                [
                    'customer_id'      => $data['customer_id'],
                    'name'             => $data['name'],
                    'measurement_type' => $data['measurement_type'],
                ],
                $data
            );

        }

        Event::dispatch('customer.measurements.create.after');

        session()->flash('success', 'Measurement has been saved successfully.');

        if ($validated['redirect']) {
            // dd($validated['redirect']);
            return redirect($validated['redirect']);
        }

        return redirect()->route('shop.customers.account.measurements.index');
    }

    /**
     * For editing the existing addresses of current logged in customer.
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $measurement = $this->customerMeasurementRepository->findOneWhere([
            'id' => $id,
            'customer_id' => auth()->guard('customer')->id(),
        ]);

        if (! $measurement) {
            abort(404);
        }

        return view('shop::customers.account.measurements.edit')
            ->with('measurement', $measurement);
    }

    /**
     * Edit's the pre-made resource of customer called Address.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(int $id, MeasurementRequest $request)
    {
        $customer = auth()->guard('customer')->user();

        if (! $customer->measurements()->find($id)) {
            session()->flash('warning', 'Invalid measurement.');
            return redirect()->route('shop.customers.account.measurements.index');
        }

        Event::dispatch('customer.measurements.update.before', $id);

        $measurement = $this->customerMeasurementRepository->update(
            $request->validated(),
            $id
        );

        Event::dispatch('customer.measurements.update.after', $measurement);

        session()->flash('success', 'Measurement has been updated successfully.');

        return redirect()->route('shop.customers.account.measurements.index');
    }

    /**
     * Delete address of the current customer.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $measurement = \Webkul\Customer\Models\Measurement::where([
            'id' => $id,
            // 'customer_id' => auth()->guard('customer')->user()->id,
        ])->first();

        if (! $measurement) {
            abort(404);
        }

        // Event::dispatch('customer.measurements.delete.before', $id);

        $del = \Webkul\Customer\Models\Measurement::destroy($id);

        // Event::dispatch('customer.measurements.delete.after', $id);

        session()->flash('success', 'Measurement has been deleted successfully.');

        // return redirect()->route('shop.customers.account.measurements.index');

        if (Request::ajax()) {
            return response()->json([
                'message' => 'Measurement has been deleted successfully.',
                'status'  => 'success',
            ]);
        }
        return redirect()->route('shop.customers.account.measurements.index');

    }
}
