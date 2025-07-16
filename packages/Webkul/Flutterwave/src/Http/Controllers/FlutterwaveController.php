<?php

namespace Webkul\Flutterwave\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Webkul\Sales\Transformers\OrderResource;

class FlutterwaveController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository
    ) {}

    /**
     * Redirects to Flutterwave.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirect(Request $request)
    {
        // return $request->all();

        $cart = Cart::getCart();
        $customer = auth()->guard('customer')->user();

        // Unique transaction ref
        $tx_ref = 'FLW_' . uniqid();

        // Store tx_ref in session for later verification
        session(['flutterwave_tx_ref' => $tx_ref]);

        // Prepare payload
        $payload = [
            'tx_ref' => $tx_ref,
            'amount' => $cart->grand_total,
            'currency' => 'NGN',
            'redirect_url' => route('flutterwave.payment.callback'),
            'payment_options' => 'card',
            'customer' => [
                'email' => $customer->email,
                'name' => $customer->first_name . ' ' . $customer->last_name,
            ],
            'customizations' => [
                'title' => config('app.name') . ' Payment',
                'description' => 'Payment for Order',
            ],
        ];

        // Call Flutterwave
        $secretKey = core()->getConfigData('sales.payment_methods.flutterwave.secret_key');
        // $secretKey = core()->getConfigData('sales.paymentmethods.flutterwave.secret_key');
        // return $secretKey;

        $response = Http::withToken($secretKey)
            ->post('https://api.flutterwave.com/v3/payments', $payload);

        $data = $response->json();


        if (($data['status'] ?? '') === 'success') {
            return redirect()->away($data['data']['link']);
        } else {
            session()->flash('error', 'Failed to initiate payment with Flutterwave.');
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Flutterwave callback (success/fail).
     *
     * @return \Illuminate\Http\Response
     */
    public function callback()
    {
        $tx_ref = request()->get('tx_ref');
        $transaction_id = request()->get('transaction_id');

        if (!$tx_ref || !$transaction_id) {
            session()->flash('error', 'Invalid payment response.');
            return redirect()->route('shop.checkout.cart.index');
        }

        $secretKey = core()->getConfigData('sales.payment_methods.flutterwave.secret_key');

        // return $secretKey;

        // Verify transaction with Flutterwave
        $verifyUrl = "https://api.flutterwave.com/v3/transactions/{$transaction_id}/verify";
        $response = Http::withToken($secretKey)->get($verifyUrl);
        $data = $response->json();

        // return $data;


        if (
            ($data['status'] ?? '') === 'success' &&
            ($data['data']['status'] ?? '') === 'successful' &&
            ($data['data']['tx_ref'] ?? '') === session('flutterwave_tx_ref')
        ) {
            //  return session('flutterwave_tx_ref');
            // Place the order using Bagisto's logic
            $cart = Cart::getCart();

            // return $cart;
            // $order = $this->orderRepository->create($cart->toArray());

             $data = (new OrderResource($cart))->jsonSerialize();

            $order = $this->orderRepository->create($data);

            // Deactivate cart
            Cart::deActivateCart();

            session()->flash('order_id', $order->id);

            // Clear tx_ref from session
            session()->forget('flutterwave_tx_ref');

            return redirect()->route('shop.checkout.onepage.success');
        } else {
            Log::error('Flutterwave Payment Failed', ['data' => $data]);
            session()->flash('error', 'Payment was not successful.');
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Payment cancellation handler.
     */
    public function cancel()
    {
        session()->flash('error', 'Flutterwave payment was cancelled.');
        return redirect()->route('shop.checkout.cart.index');
    }
}
