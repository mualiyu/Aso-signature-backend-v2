<?php

use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Support\Facades\Route;
use Webkul\Alteration\Http\Controllers\Shop\ClaimController;
use Webkul\Alteration\Http\Controllers\Shop\LocatorController;
use Webkul\Alteration\Http\Middleware\EnsureAlterationEnabled;

Route::group(['middleware' => ['web', 'locale', 'theme', 'currency', ConvertEmptyStringsToNull::class]], function () {
    /**
     * Only available while the programme is switched on (Admin → Configure → Sales → Alterations).
     */
    Route::middleware(EnsureAlterationEnabled::class)->group(function () {
        /**
         * Tailor locator. Not response-cached: partner data changes from the admin.
         */
        Route::get('alteration-partners', [LocatorController::class, 'index'])
            ->name('shop.alterations.partners.index');

        Route::get('api/alteration-partners', [LocatorController::class, 'search'])
            ->name('shop.api.alterations.partners.search');

        Route::group(['middleware' => ['customer'], 'prefix' => 'customer/account'], function () {
            Route::controller(ClaimController::class)->group(function () {
                Route::get('alterations', 'index')->name('shop.customers.account.alterations.index');

                Route::get('orders/{orderId}/alteration', 'create')->name('shop.customers.account.alterations.create');

                Route::post('orders/{orderId}/alteration', 'store')->name('shop.customers.account.alterations.store');
            });
        });
    });

    /**
     * A customer's existing request stays viewable when the programme is switched off, so the
     * links in claim emails keep working.
     */
    Route::group(['middleware' => ['customer'], 'prefix' => 'customer/account'], function () {
        Route::get('alterations/view/{id}', [ClaimController::class, 'view'])
            ->name('shop.customers.account.alterations.view');
    });
});
