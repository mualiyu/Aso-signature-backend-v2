<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Aso Measurement Service
    |--------------------------------------------------------------------------
    |
    | Configuration for the standalone measurement wizard microservice. The
    | store issues short-lived signed launch tokens and verifies the signed
    | callback the service sends back when a profile is completed.
    |
    */

    // Public base URL of the wizard SPA (no trailing slash),
    // e.g. http://localhost:8200
    'url' => env('MEASUREMENT_SERVICE_URL', ''),

    // Shared HMAC secret. MUST match the service's SHARED_SECRET.
    'secret' => env('MEASUREMENT_SERVICE_SECRET', ''),

    // Preferred estimator method: auto | formula | llm | model
    'method' => env('MEASUREMENT_SERVICE_METHOD', 'auto'),

    // Launch token lifetime in seconds.
    'token_ttl' => (int) env('MEASUREMENT_SERVICE_TOKEN_TTL', 600),

    // Max allowed clock skew (seconds) when verifying callback signatures.
    'callback_tolerance' => (int) env('MEASUREMENT_SERVICE_CALLBACK_TOLERANCE', 300),
];
