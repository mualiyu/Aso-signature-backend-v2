<?php

namespace Webkul\Stripe\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe as StripeClient;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class StripeController extends Controller
{
    /**
     * Zero-decimal currencies per Stripe docs.
     *
     * @var array<int, string>
     */
    protected array $zeroDecimalCurrencies = [
        'BIF',
        'CLP',
        'DJF',
        'GNF',
        'JPY',
        'KMF',
        'KRW',
        'MGA',
        'PYG',
        'RWF',
        'UGX',
        'VND',
        'VUV',
        'XAF',
        'XOF',
        'XPF',
    ];

    public function __construct(
        protected OrderRepository $orderRepository
    ) {}

    /**
     * Redirect customer to Stripe Checkout.
     */
    public function redirect()
    {
        $cart = Cart::getCart();

        if (! $cart) {
            session()->flash('error', trans('shop::app.checkout.cart.index.empty-product'));

            return redirect()->route('shop.checkout.cart.index');
        }

        $secretKey = core()->getConfigData('sales.payment_methods.stripe.secret_key');

        if (! $secretKey) {
            session()->flash('error', 'Stripe is not configured. Please contact the store administrator.');

            return redirect()->route('shop.checkout.cart.index');
        }

        $currentCurrency = session('currency') ?: core()->getCurrentCurrencyCode();
        $txRef = 'STRIPE_'.uniqid();

        session(['stripe_tx_ref' => $txRef]);

        $customerEmail = $this->resolveCustomerEmail($cart);
        $customerName = $this->resolveCustomerName($cart);

        try {
            StripeClient::setApiKey($secretKey);

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items'           => [[
                    'price_data' => [
                        'currency'     => strtolower($currentCurrency),
                        'product_data' => [
                            'name' => config('app.name').' — Order Payment',
                        ],
                        'unit_amount' => $this->convertToStripeAmount($cart->grand_total, $currentCurrency),
                    ],
                    'quantity' => 1,
                ]],
                'mode'           => 'payment',
                'success_url'    => route('stripe.payment.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'     => route('stripe.payment.cancel'),
                'customer_email' => $customerEmail,
                'metadata'       => [
                    'tx_ref'        => $txRef,
                    'customer_name' => $customerName,
                ],
            ]);

            return redirect()->away($session->url);
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment initiation failed: '.$e->getMessage(), [
                'stripe_code' => $e->getStripeCode(),
            ]);

            session()->flash('error', 'Unable to connect to Stripe. Please try again.');

            return redirect()->route('shop.checkout.cart.index');
        } catch (\Exception $e) {
            Log::error('Stripe payment initiation failed: '.$e->getMessage());

            session()->flash('error', 'Unable to connect to payment gateway.');

            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Stripe Checkout success callback.
     */
    public function success()
    {
        $sessionId = request()->get('session_id');

        if (! $sessionId) {
            session()->flash('error', 'Invalid payment response.');

            return redirect()->route('shop.checkout.cart.index');
        }

        $secretKey = core()->getConfigData('sales.payment_methods.stripe.secret_key');

        try {
            StripeClient::setApiKey($secretKey);

            $session = Session::retrieve($sessionId);
        } catch (ApiErrorException $e) {
            Log::error('Stripe session retrieval failed: '.$e->getMessage());

            session()->flash('error', 'Payment verification failed.');

            return redirect()->route('shop.checkout.cart.index');
        }

        $expectedTxRef = session('stripe_tx_ref');
        $sessionTxRef = $session->metadata['tx_ref'] ?? null;

        if (
            $session->payment_status === 'paid'
            && $sessionTxRef
            && $expectedTxRef
            && $sessionTxRef === $expectedTxRef
        ) {
            $cart = Cart::getCart();

            if (! $cart) {
                session()->flash('error', 'Your cart has expired. Please contact support if you were charged.');

                return redirect()->route('shop.checkout.cart.index');
            }

            $data = (new OrderResource($cart))->jsonSerialize();

            $order = $this->orderRepository->create($data);

            Cart::deActivateCart();

            session()->flash('order_id', $order->id);
            session()->forget('stripe_tx_ref');

            return redirect()->route('shop.checkout.onepage.success');
        }

        Log::error('Stripe payment verification failed', [
            'payment_status'  => $session->payment_status ?? null,
            'session_tx_ref'  => $sessionTxRef,
            'expected_tx_ref' => $expectedTxRef,
        ]);

        session()->flash('error', 'Payment was not successful.');

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Payment cancellation handler.
     */
    public function cancel()
    {
        session()->forget('stripe_tx_ref');

        session()->flash('error', 'Stripe payment was cancelled.');

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Convert cart total to Stripe's smallest currency unit.
     */
    protected function convertToStripeAmount(float $amount, string $currency): int
    {
        if (in_array(strtoupper($currency), $this->zeroDecimalCurrencies, true)) {
            return (int) round($amount);
        }

        return (int) round($amount * 100);
    }

    /**
     * Resolve customer email from auth or cart billing address.
     */
    protected function resolveCustomerEmail($cart): ?string
    {
        $customer = auth()->guard('customer')->user();

        if ($customer?->email) {
            return $customer->email;
        }

        return $cart->billing_address?->email
            ?? $cart->customer_email
            ?? null;
    }

    /**
     * Resolve customer display name.
     */
    protected function resolveCustomerName($cart): string
    {
        $customer = auth()->guard('customer')->user();

        if ($customer) {
            return trim($customer->first_name.' '.$customer->last_name);
        }

        if ($cart->billing_address) {
            return trim($cart->billing_address->first_name.' '.$cart->billing_address->last_name);
        }

        return 'Customer';
    }
}
