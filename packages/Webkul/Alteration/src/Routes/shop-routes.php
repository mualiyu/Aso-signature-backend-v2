<?php

use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Support\Facades\Route;
use Webkul\Alteration\Http\Controllers\Shop\ClaimController;
use Webkul\Alteration\Http\Controllers\Shop\LocatorController;

Route::group(['middleware' => ['web', 'locale', 'theme', 'currency', ConvertEmptyStringsToNull::class]], function () {
    /**
     * Tailor locator. Not response-cached: partner data changes from the admin.
     */
    Route::get('alteration-partners', [LocatorController::class, 'index'])
        ->name('shop.alterations.partners.index');

    Route::get('api/alteration-partners', [LocatorController::class, 'search'])
        ->name('shop.api.alterations.partners.search');

    /**
     * Customer alteration requests.
     */
    Route::group(['middleware' => ['customer'], 'prefix' => 'customer/account'], function () {
        Route::controller(ClaimController::class)->group(function () {
            Route::get('alterations', 'index')->name('shop.customers.account.alterations.index');

            Route::get('alterations/view/{id}', 'view')->name('shop.customers.account.alterations.view');

            Route::get('orders/{orderId}/alteration', 'create')->name('shop.customers.account.alterations.create');

            Route::post('orders/{orderId}/alteration', 'store')->name('shop.customers.account.alterations.store');
        });
    });
});
