<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Webkul\Customer\Services\MeasurementService;
use Webkul\Shop\Http\Controllers\Controller;
use Webkul\Shop\Http\Requests\Customer\MeasurementRequest;

class MeasurementController extends Controller
{
    public function __construct(
        protected MeasurementService $measurementService
    ) {}

    /**
     * Get customer measurements and form metadata.
     */
    public function index(): JsonResponse
    {
        $customer = auth()->guard('customer')->user();

        if (! $customer) {
            return response()->json([
                'message' => 'Unauthorized',
                'status'  => 'error',
            ], 401);
        }

        $profile = $this->measurementService->resolveProfile(
            $customer,
            (int) request()->query('profile_id') ?: null
        );

        $payload = $this->measurementService->buildFormPayload($customer, $profile);

        return response()->json([
            'data' => [
                'measurements' => $profile ? $profile->measurements : collect(),
                'payload'      => $payload,
                'completeness' => $payload['completeness'],
            ],
            'status' => 'success',
        ]);
    }

    /**
     * Save customer measurements from checkout or API clients.
     */
    public function store(MeasurementRequest $request): JsonResponse
    {
        $customer = auth()->guard('customer')->user();

        if (! $customer) {
            return response()->json([
                'message' => 'Unauthorized',
                'status'  => 'error',
            ], 401);
        }

        $result = $this->measurementService->save($customer, $request->validated());

        return response()->json([
            'message' => 'Measurements saved successfully.',
            'status'  => 'success',
            'data'    => $result,
        ]);
    }
}
