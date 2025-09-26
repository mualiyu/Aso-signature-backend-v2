<?php

namespace Webkul\Shop\Http\Controllers\API;

use Webkul\Shop\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeasurementController extends Controller
{
    /**
     * Get customer measurements.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $customer = auth()->guard('customer')->user();

        if (!$customer) {
            return response()->json([
                'message' => 'Unauthorized',
                'status' => 'error'
            ], 401);
        }

        $measurements = $customer->measurements;

        return response()->json([
            'data' => $measurements,
            'status' => 'success'
        ]);
    }
}
