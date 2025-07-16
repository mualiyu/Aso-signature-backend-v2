<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
        // Add your URIs here that you want to exclude from CSRF verification
        // Example:
        'https://backend.asosignature.com/api/*',
        'http://aso-signature-backend.test/api/*',
    ];
}
