<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Webkul\Checkout\Facades\Cart;
use Webkul\Customer\Data\MeasurementFields;
use Webkul\Customer\Services\MeasurementService;
use Webkul\Shop\Http\Controllers\Controller;
use Webkul\Shop\Http\Requests\Customer\MeasurementProfileRequest;
use Webkul\Shop\Http\Requests\Customer\MeasurementRequest;

class MeasurementProfileController extends Controller
{
    public function __construct(
        protected MeasurementService $measurementService
    ) {}

    /**
     * List the customer's measurement profiles.
     */
    public function index(): JsonResponse
    {
        $customer = auth()->guard('customer')->user();

        if (! $customer) {
            return $this->unauthorized();
        }

        return response()->json([
            'data' => [
                'profiles'       => $this->measurementService->profileSummaries($customer),
                'fitPreferences' => MeasurementFields::fitPreferenceOptions(),
                'fitNoteOptions' => MeasurementFields::fitNoteOptions(),
            ],
            'status' => 'success',
        ]);
    }

    /**
     * Create a measurement profile.
     */
    public function store(MeasurementProfileRequest $request): JsonResponse
    {
        $customer = auth()->guard('customer')->user();

        if (! $customer) {
            return $this->unauthorized();
        }

        $profile = $this->measurementService->createProfile($customer, $request->validated());

        return response()->json([
            'message' => 'Measurement profile created successfully.',
            'status'  => 'success',
            'data'    => [
                'profile'  => $this->measurementService->summarizeProfile($profile),
                'profiles' => $this->measurementService->profileSummaries($customer),
            ],
        ], 201);
    }

    /**
     * Update a measurement profile.
     */
    public function update(MeasurementProfileRequest $request, int $id): JsonResponse
    {
        $customer = auth()->guard('customer')->user();

        if (! $customer) {
            return $this->unauthorized();
        }

        $profile = $customer->measurementProfiles()->find($id);

        if (! $profile) {
            return $this->profileNotFound();
        }

        $profile = $this->measurementService->updateProfile($customer, $profile, $request->validated());

        return response()->json([
            'message' => 'Measurement profile updated successfully.',
            'status'  => 'success',
            'data'    => [
                'profile'  => $this->measurementService->summarizeProfile($profile),
                'profiles' => $this->measurementService->profileSummaries($customer),
            ],
        ]);
    }

    /**
     * Delete a measurement profile.
     */
    public function destroy(int $id): JsonResponse
    {
        $customer = auth()->guard('customer')->user();

        if (! $customer) {
            return $this->unauthorized();
        }

        $profile = $customer->measurementProfiles()->find($id);

        if (! $profile) {
            return $this->profileNotFound();
        }

        $this->measurementService->deleteProfile($customer, $profile);

        return response()->json([
            'message' => 'Measurement profile deleted successfully.',
            'status'  => 'success',
            'data'    => [
                'profiles' => $this->measurementService->profileSummaries($customer),
            ],
        ]);
    }

    /**
     * Save measurements for a specific profile.
     */
    public function saveMeasurements(MeasurementRequest $request, int $id): JsonResponse
    {
        $customer = auth()->guard('customer')->user();

        if (! $customer) {
            return $this->unauthorized();
        }

        if (! $customer->measurementProfiles()->where('id', $id)->exists()) {
            return $this->profileNotFound();
        }

        $result = $this->measurementService->save(
            $customer,
            array_merge($request->validated(), ['profile_id' => $id, 'create_profile' => false])
        );

        return response()->json([
            'message' => 'Measurements saved successfully.',
            'status'  => 'success',
            'data'    => $result,
        ]);
    }

    /**
     * Assign (or clear) a measurement profile on a cart item.
     */
    public function assignToCartItem(int $id): JsonResponse
    {
        $customer = auth()->guard('customer')->user();

        if (! $customer) {
            return $this->unauthorized();
        }

        $cart = Cart::getCart();

        if (! $cart) {
            return response()->json([
                'message' => 'No active cart found.',
                'status'  => 'error',
            ], 400);
        }

        $item = $cart->items()->where('id', $id)->first();

        if (! $item) {
            return response()->json([
                'message' => 'Cart item not found.',
                'status'  => 'error',
            ], 404);
        }

        $profileId = request()->input('measurement_profile_id');
        $profile = null;

        if ($profileId) {
            $profile = $customer->measurementProfiles()->find((int) $profileId);

            if (! $profile) {
                return $this->profileNotFound();
            }
        }

        $additional = $item->additional ?? [];

        if ($profile) {
            $additional['measurement_profile_id'] = $profile->id;
        } else {
            unset($additional['measurement_profile_id']);
        }

        $item->measurement_profile_id = $profile?->id;
        $item->additional = $additional;
        $item->save();

        return response()->json([
            'message' => $profile
                ? 'Measurement profile assigned to item.'
                : 'Measurement profile removed from item.',
            'status' => 'success',
            'data'   => [
                'cart_item_id'           => $item->id,
                'measurement_profile_id' => $profile?->id,
            ],
        ]);
    }

    /**
     * Shared unauthorized response.
     */
    protected function unauthorized(): JsonResponse
    {
        return response()->json([
            'message' => 'Unauthorized',
            'status'  => 'error',
        ], 401);
    }

    /**
     * Shared profile-not-found response.
     */
    protected function profileNotFound(): JsonResponse
    {
        return response()->json([
            'message' => 'Measurement profile not found.',
            'status'  => 'error',
        ], 404);
    }
}
