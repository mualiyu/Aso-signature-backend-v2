<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Webkul\Shop\Helpers\HowToMeasure;

class HowToMeasureController extends APIController
{
    /**
     * The "How to Measure" content, consumed by the global modal
     * (`v-how-to-measure-modal`) the first time it is opened.
     */
    public function index(HowToMeasure $howToMeasure): JsonResponse
    {
        return response()->json([
            'data' => [
                'measurements' => $howToMeasure->measurements(),
                'videos'       => $howToMeasure->videos(),
            ],
        ]);
    }
}
