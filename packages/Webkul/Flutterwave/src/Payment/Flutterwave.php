<?php

namespace Webkul\Flutterwave\Payment;

use Webkul\Payment\Payment\Payment;
use Illuminate\Support\Facades\Storage;

class Flutterwave extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code  = 'flutterwave';

    public function getRedirectUrl()
    {
        return route('flutterwave.payment.redirect');
    }

    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/paypal.png', 'shop');
    }
}
