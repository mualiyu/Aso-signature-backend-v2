<?php

use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Support\Facades\Route;
use Webkul\Alteration\Http\Controllers\Admin\ClaimController;
use Webkul\Alteration\Http\Controllers\Admin\FixTypeController;
use Webkul\Alteration\Http\Controllers\Admin\PartnerController;

/**
 * The app kernel does not convert empty strings to null globally, so it is applied here:
 * blank form fields (e.g. "no partner selected") must reach the database as NULL.
 */
Route::group(['middleware' => ['web', 'admin', ConvertEmptyStringsToNull::class], 'prefix' => config('app.admin_url').'/alterations'], function () {
    /**
     * Alteration claims queue.
     */
    Route::controller(ClaimController::class)->prefix('claims')->group(function () {
        Route::get('', 'index')->name('admin.alterations.claims.index');

        Route::get('view/{id}', 'view')->name('admin.alterations.claims.view');

        Route::put('view/{id}', 'update')->name('admin.alterations.claims.update');
    });

    /**
     * Alteration partner tracker.
     */
    Route::controller(PartnerController::class)->prefix('partners')->group(function () {
        Route::get('', 'index')->name('admin.alterations.partners.index');

        Route::get('create', 'create')->name('admin.alterations.partners.create');

        Route::post('create', 'store')->name('admin.alterations.partners.store');

        Route::get('edit/{id}', 'edit')->name('admin.alterations.partners.edit');

        Route::put('edit/{id}', 'update')->name('admin.alterations.partners.update');

        Route::delete('edit/{id}', 'destroy')->name('admin.alterations.partners.delete');

        Route::post('mass-update', 'massUpdate')->name('admin.alterations.partners.mass_update');
    });

    /**
     * Fix types and ASO cost caps.
     */
    Route::controller(FixTypeController::class)->prefix('fix-types')->group(function () {
        Route::get('', 'index')->name('admin.alterations.fix_types.index');

        Route::put('', 'update')->name('admin.alterations.fix_types.update');
    });
});
