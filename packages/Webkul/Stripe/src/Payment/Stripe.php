<?php

namespace Webkul\Stripe\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

class Stripe extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'stripe';

    /**
     * Get redirect URL for Stripe Checkout.
     */
    public function getRedirectUrl()
    {
        return route('stripe.payment.redirect');
    }

    /**
     * Get payment method logo.
     */
    public function getImage()
    {
        $url = $this->getConfigData('logo');

        return $url ? Storage::url($url) : bagisto_asset('images/stripe.png', 'shop');
    }
}
