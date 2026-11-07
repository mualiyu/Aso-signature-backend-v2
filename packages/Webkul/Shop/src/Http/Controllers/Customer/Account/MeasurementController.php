<?php

namespace Webkul\Shop\Http\Controllers\Customer\Account;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Webkul\Customer\Models\Measurement;
use Webkul\Customer\Services\MeasurementService;
use Webkul\Shop\Http\Controllers\Controller;
use Webkul\Shop\Http\Requests\Customer\MeasurementRequest;

class MeasurementController extends Controller
{
    public function __construct(
        protected MeasurementService $measurementService
    ) {}

  /**
   * Canonical measurements page.
   */
    public function index()
    {
        $customer = auth()->guard('customer')->user();

        $profile = $this->measurementService->resolveProfile(
            $customer,
            (int) request()->query('profile') ?: null
        );

        return view('shop::customers.account.measurements.create', [
            'payload' => $this->measurementService->buildFormPayload($customer, $profile),
        ]);
    }

  /**
   * Alias for the canonical measurements page.
   */
    public function create()
    {
        return $this->index();
    }

  /**
   * Store customer measurements.
   */
    public function store(MeasurementRequest $request): JsonResponse|RedirectResponse
    {
        $customer = auth()->guard('customer')->user();

        Event::dispatch('customer.measurements.create.before');

        $result = $this->measurementService->save($customer, $request->validated());

        Event::dispatch('customer.measurements.create.after');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Measurements saved successfully.',
                'status'  => 'success',
                'data'    => $result,
            ]);
        }

        session()->flash('success', 'Measurements saved successfully.');

        if ($request->input('redirect')) {
            return redirect($request->input('redirect'));
        }

        return redirect()->route('shop.customers.account.measurements.index');
    }

  /**
   * Delete a custom measurement.
   */
    public function destroy(int $id): JsonResponse|RedirectResponse
    {
        $customer = auth()->guard('customer')->user();

        $measurement = Measurement::where([
            'id'          => $id,
            'customer_id' => $customer->id,
        ])->first();

        if (! $measurement) {
            abort(404);
        }

        $measurement->delete();

        if (Request::ajax() || request()->expectsJson()) {
            return response()->json([
                'message' => 'Measurement deleted successfully.',
                'status'  => 'success',
            ]);
        }

        session()->flash('success', 'Measurement deleted successfully.');

        return redirect()->route('shop.customers.account.measurements.index');
    }
}
