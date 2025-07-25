<?php

namespace Webkul\Flutterwave\Payment;

use Webkul\Payment\Payment\Payment;

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
}
