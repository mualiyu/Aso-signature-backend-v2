<?php

use Illuminate\Support\Facades\Route;
use Webkul\Admin\Http\Controllers\Customers\AddressController;
use Webkul\Admin\Http\Controllers\Customers\Customer\CartController;
use Webkul\Admin\Http\Controllers\Customers\Customer\CompareController;
use Webkul\Admin\Http\Controllers\Customers\Customer\OrderController;
use Webkul\Admin\Http\Controllers\Customers\Customer\WishlistController;

use Webkul\Admin\Http\Controllers\Designers\DesignerController;
use Webkul\Admin\Http\Controllers\Customers\CustomerGroupController;
use Webkul\Admin\Http\Controllers\Customers\ReviewController;

/**
 * Customers routes.
 */
Route::group(['middleware' => ['admin'], 'prefix' => config('app.admin_url').'/designers'], function () {
    Route::prefix('designers')->group(function () {
        /**
         * Customer management routes.
         */
        Route::controller(DesignerController::class)->group(function () {
            Route::get('', 'index')->name('admin.designers.designers.index');

            Route::get('view/{id}', 'show')->name('admin.designers.designers.view');

            Route::post('create', 'store')->name('admin.designers.designers.store');

            Route::get('search', 'search')->name('admin.designers.designers.search');

            Route::get('login-as-customer/{id}', 'loginAsCustomer')->name('admin.designers.designers.login_as_customer');

            Route::post('note/{id}', 'storeNotes')->name('admin.customer.note.store');

            Route::put('edit/{id}', 'update')->name('admin.designers.designers.update');

            Route::post('mass-delete', 'massDestroy')->name('admin.designers.designers.mass_delete');

            Route::post('mass-update', 'massUpdate')->name('admin.designers.designers.mass_update');

            Route::post('{id}', 'destroy')->name('admin.designers.designers.delete');

            Route::controller(WishlistController::class)->group(function () {
                Route::get('{id}/wishlist-items', 'items')->name('admin.designers.designers.wishlist.items');

                Route::delete('{id}/wishlist-items', 'destroy')->name('admin.designers.designers.wishlist.items.delete');
            });

            Route::controller(CompareController::class)->group(function () {
                Route::get('{id}/compare-items', 'items')->name('admin.designers.designers.compare.items');

                Route::delete('{id}/compare-items', 'destroy')->name('admin.designers.designers.compare.items.delete');
            });

            Route::controller(CartController::class)->prefix('{id}/cart')->group(function () {
                Route::post('create', 'store')->name('admin.designers.designers.cart.store');

                Route::get('items', 'items')->name('admin.designers.designers.cart.items');

                Route::delete('items', 'destroy')->name('admin.designers.designers.cart.items.delete');
            });

            Route::controller(OrderController::class)->group(function () {
                Route::get('{id}/recent-order-items', 'recentItems')->name('admin.designers.designers.orders.recent_items');
            });
        });

        /**
         * Customer's addresses routes.
         */
        Route::controller(AddressController::class)->group(function () {
            Route::prefix('{id}/addresses')->group(function () {
                Route::get('', 'index')->name('admin.designers.designers.addresses.index');

                Route::get('create', 'create')->name('admin.designers.designers.addresses.create');

                Route::post('create', 'store')->name('admin.designers.designers.addresses.store');
            });

            Route::prefix('addresses')->group(function () {
                Route::get('edit/{id}', 'edit')->name('admin.designers.designers.addresses.edit');

                Route::put('edit/{id}', 'update')->name('admin.designers.designers.addresses.update');

                Route::post('default/{id}', 'makeDefault')->name('admin.designers.designers.addresses.set_default');

                Route::post('delete/{id}', 'destroy')->name('admin.designers.designers.addresses.delete');
            });
        });
    });

    /**
     * Customer's reviews routes.
     */
    Route::controller(ReviewController::class)->prefix('reviews')->group(function () {
        Route::get('', 'index')->name('admin.designers.designers.review.index');

        Route::get('edit/{id}', 'edit')->name('admin.designers.designers.review.edit');

        Route::put('edit/{id}', 'update')->name('admin.designers.designers.review.update');

        Route::delete('/{id}', 'destroy')->name('admin.designers.designers.review.delete');

        Route::post('mass-delete', 'massDestroy')->name('admin.designers.designers.review.mass_delete');

        Route::post('mass-update', 'massUpdate')->name('admin.designers.designers.review.mass_update');
    });

    /**
     * Customer groups routes.
     */
    Route::controller(CustomerGroupController::class)->prefix('groups')->group(function () {
        Route::get('', 'index')->name('admin.designers.groups.index');

        Route::post('create', 'store')->name('admin.designers.groups.store');

        Route::put('edit', 'update')->name('admin.designers.groups.update');

        Route::delete('delete/{id}', 'destroy')->name('admin.designers.groups.delete');
    });
});
