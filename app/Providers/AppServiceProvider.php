<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\DynamicComponent;
use Webkul\User\Models\AdminProxy;
use Webkul\User\Models\Admin;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure Laravel Pulse (and any view) can resolve <x-dynamic-component>
        Blade::component('dynamic-component', DynamicComponent::class);

        Schema::defaultStringLength(191);

        ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
            Artisan::call('db:seed');
        });

        // Gate::define('viewPulse', function (Admin $user) {
        //     return $user->isAdmin();
        // });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Only enable debug tools in non-production environments
        if (app()->environment('production')) {
            return;
        }

        $allowedIPs = array_map('trim', explode(',', config('app.debug_allowed_ips')));

        $allowedIPs = array_filter($allowedIPs);

        if (empty($allowedIPs)) {
            return;
        }

        if (in_array(Request::ip(), $allowedIPs)) {
            \Debugbar::enable();
        } else {
            \Debugbar::disable();
        }
    }
}
