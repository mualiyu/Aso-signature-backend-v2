<?php

namespace Webkul\Alteration\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Webkul\Alteration\AlterationFeature;

class EnsureAlterationEnabled
{
    /**
     * Hide storefront alteration pages (404) while the programme is switched off.
     */
    public function handle(Request $request, Closure $next)
    {
        abort_unless(AlterationFeature::enabled(), 404);

        return $next($request);
    }
}
