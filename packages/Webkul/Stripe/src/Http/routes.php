<?php

use Illuminate\Support\Facades\Route;
use Webkul\Stripe\Http\Controllers\StripeController;

Route::group(['middleware' => ['web']], function () {
    Route::get('/stripe/payment/redirect', [StripeController::class, 'redirect'])->name('stripe.payment.redirect');

    Route::get('/stripe/payment/success', [StripeController::class, 'success'])->name('stripe.payment.success');

    Route::get('/stripe/payment/cancel', [StripeController::class, 'cancel'])->name('stripe.payment.cancel');
});
