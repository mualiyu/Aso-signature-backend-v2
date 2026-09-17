<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    /**
     * Whether the visitor was sent to sign in or register from the checkout page.
     * The account pages use this to explain why an account is needed and to
     * promise the customer they will be taken back to checkout.
     */
    protected function isCheckoutIntended(): bool
    {
        return str_starts_with((string) session('url.intended'), route('shop.checkout.onepage.index'));
    }
}
