<?php

use Illuminate\Support\Facades\Route;
use Webkul\Flutterwave\Http\Controllers\FlutterwaveController;
// use Webkul\Shop\Http\Controllers\FlutterwaveController;
use Webkul\Flutterwave\Http\Controllers\SmartButtonController;

Route::group(['middleware' => ['web']], function () {
    // Route::prefix('paypal/standard')->group(function () {
    //     Route::get('/redirect', [StandardController::class, 'redirect'])->name('paypal.standard.redirect');

    //     Route::get('/success', [StandardController::class, 'success'])->name('paypal.standard.success');

    //     Route::get('/cancel', [StandardController::class, 'cancel'])->name('paypal.standard.cancel');
    // });

    // Route::prefix('paypal/smart-button')->group(function () {
    //     Route::get('/create-order', [SmartButtonController::class, 'createOrder'])->name('paypal.smart-button.create-order');

    //     Route::post('/capture-order', [SmartButtonController::class, 'captureOrder'])->name('paypal.smart-button.capture-order');
    // });

    Route::get('/flutterwave/payment/redirect', [FlutterwaveController::class, 'redirect'])->name('flutterwave.payment.redirect');
    Route::get('/flutterwave/payment/callback', [FlutterwaveController::class, 'callback'])->name('flutterwave.payment.callback');
    Route::get('/flutterwave/payment/cancel', [FlutterwaveController::class, 'cancel'])->name('flutterwave.payment.cancel');

});
