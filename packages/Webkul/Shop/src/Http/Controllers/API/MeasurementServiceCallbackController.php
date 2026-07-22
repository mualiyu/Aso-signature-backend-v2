<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Customer\Services\MeasurementService;
use Webkul\Customer\Services\MeasurementWizardService;
use Webkul\Shop\Http\Controllers\Controller;

class MeasurementServiceCallbackController extends Controller
{
    public function __construct(
        protected MeasurementWizardService $wizardService,
        protected MeasurementService $measurementService,
        protected CustomerRepository $customerRepository
    ) {}

    /**
     * Persist a finished profile pushed by the measurement service.
     *
     * Authentication is via HMAC signature over the raw body (not a session).
     * The trusted customer id is recovered from the launch token echoed in the
     * payload, never from a client-supplied id.
     */
    public function handle(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();

        $valid = $this->wizardService->verifyCallbackSignature(
            $request->header('X-Signature'),
            $request->header('X-Timestamp'),
            $rawBody
        );

        if (! $valid) {
            return response()->json([
                'message' => 'Invalid signature.',
                'status'  => 'error',
            ], 401);
        }

        $data = json_decode($rawBody, true) ?: [];

        $claims = $this->wizardService->decodeToken($data['token'] ?? '');

        if (! $claims || empty($claims['sub'])) {
            return response()->json([
                'message' => 'Invalid or expired token.',
                'status'  => 'error',
            ], 401);
        }

        $customer = $this->customerRepository->find((int) $claims['sub']);

        if (! $customer) {
            return response()->json([
                'message' => 'Customer not found.',
                'status'  => 'error',
            ], 404);
        }

        $payload = [
            'profile_id'      => $data['profile_id'] ?? null,
            'create_profile'  => (bool) ($data['create_profile'] ?? true),
            'profile_name'    => $data['profile_name'] ?? 'My Measurements',
            'gender'          => $data['gender'] ?? $customer->gender,
            'unit'            => $data['unit'] ?? null,
            'fit_preference'  => $data['fit_preference'] ?? null,
            'fit_notes'       => $data['fit_notes'] ?? null,
            'fit_notes_other' => $data['fit_notes_other'] ?? null,
            'measurements'    => $data['measurements'] ?? [],
            'custom'          => $data['custom'] ?? [],
        ];

        $result = $this->measurementService->save($customer, $payload);

        return response()->json([
            'message' => 'Profile saved.',
            'status'  => 'success',
            'data'    => [
                'profile' => $result['profile'] ?? null,
            ],
        ]);
    }
}
