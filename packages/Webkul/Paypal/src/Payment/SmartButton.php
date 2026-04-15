<?php

namespace Webkul\Paypal\Payment;

use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Payments\CapturesRefundRequest;

class SmartButton extends Paypal
{
    /**
     * Client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * Client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'paypal_smart_button';

    /**
     * Paypal partner attribution id.
     *
     * @var string
     */
    protected $paypalPartnerAttributionId = 'Bagisto_Cart';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Returns PayPal HTTP client instance with environment that has access
     * credentials context. Use this instance to invoke PayPal APIs, provided the
     * credentials have access.
     *
     * @return PayPalCheckoutSdk\Core\PayPalHttpClient
     */
    public function client()
    {
        return new PayPalHttpClient($this->environment());
    }

    /**
     * Create order for approval of client.
     *
     * @param  array  $body
     * @return HttpResponse
     */
    public function createOrder($body)
    {
        $request = new OrdersCreateRequest;
        $request->headers['PayPal-Partner-Attribution-Id'] = $this->paypalPartnerAttributionId;
        $request->prefer('return=representation');
        $request->body = $body;

        Log::info('PayPal SmartButton: Creating order', [
            'body_intent' => $body['intent'] ?? null,
            'currency'    => $body['purchase_units'][0]['amount']['currency_code'] ?? null,
            'amount'      => $body['purchase_units'][0]['amount']['value'] ?? null,
        ]);

        try {
            $response = $this->client()->execute($request);

            Log::info('PayPal SmartButton: Order created successfully', [
                'paypal_order_id' => $response->result->id ?? null,
                'status'          => $response->result->status ?? null,
                'status_code'     => $response->statusCode ?? null,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('PayPal SmartButton: Order creation failed', [
                'error' => $e->getMessage(),
                'code'  => $e->getCode(),
            ]);

            throw $e;
        }
    }

    /**
     * Capture order after approval.
     *
     * @param  string  $orderId
     * @return HttpResponse
     */
    public function captureOrder($orderId)
    {
        $request = new OrdersCaptureRequest($orderId);

        $request->headers['PayPal-Partner-Attribution-Id'] = $this->paypalPartnerAttributionId;
        $request->prefer('return=representation');

        Log::info('PayPal SmartButton: Capturing order', ['order_id' => $orderId]);

        try {
            $response = $this->client()->execute($request);

            Log::info('PayPal SmartButton: Order captured successfully', [
                'order_id'    => $orderId,
                'status'      => $response->result->status ?? null,
                'status_code' => $response->statusCode ?? null,
                'capture_id'  => $response->result->purchase_units[0]->payments->captures[0]->id ?? null,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('PayPal SmartButton: Order capture failed', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
                'code'     => $e->getCode(),
            ]);

            throw $e;
        }
    }

    /**
     * Get order details.
     *
     * @param  string  $orderId
     * @return HttpResponse
     */
    public function getOrder($orderId)
    {
        Log::info('PayPal SmartButton: Getting order details', ['order_id' => $orderId]);

        try {
            $response = $this->client()->execute(new OrdersGetRequest($orderId));

            Log::info('PayPal SmartButton: Got order details', [
                'order_id'    => $orderId,
                'status'      => $response->result->status ?? null,
                'status_code' => $response->statusCode ?? null,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('PayPal SmartButton: Get order failed', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
                'code'     => $e->getCode(),
            ]);

            throw $e;
        }
    }

    /**
     * Get capture id.
     *
     * @param  string  $orderId
     * @return string
     */
    public function getCaptureId($orderId)
    {
        $paypalOrderDetails = $this->getOrder($orderId);

        return $paypalOrderDetails->result->purchase_units[0]->payments->captures[0]->id;
    }

    /**
     * Refund order.
     *
     * @return HttpResponse
     */
    public function refundOrder($captureId, $body = [])
    {
        $request = new CapturesRefundRequest($captureId);

        $request->headers['PayPal-Partner-Attribution-Id'] = $this->paypalPartnerAttributionId;
        $request->body = $body;

        Log::info('PayPal SmartButton: Refunding order', [
            'capture_id' => $captureId,
            'body'       => $body,
        ]);

        try {
            $response = $this->client()->execute($request);

            Log::info('PayPal SmartButton: Refund successful', [
                'capture_id'  => $captureId,
                'status_code' => $response->statusCode ?? null,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('PayPal SmartButton: Refund failed', [
                'capture_id' => $captureId,
                'error'      => $e->getMessage(),
                'code'       => $e->getCode(),
            ]);

            throw $e;
        }
    }

    /**
     * Return paypal redirect url.
     *
     * @return string
     */
    public function getRedirectUrl() {}

    /**
     * Set up and return PayPal PHP SDK environment with PayPal access credentials.
     * This sample uses SandboxEnvironment. In production, use LiveEnvironment.
     *
     * @return PayPalCheckoutSdk\Core\SandboxEnvironment|PayPalCheckoutSdk\Core\ProductionEnvironment
     */
    protected function environment()
    {
        $isSandbox = $this->getConfigData('sandbox') ?: false;

        Log::info('PayPal SmartButton: Initializing environment', [
            'sandbox'          => $isSandbox,
            'client_id_set'    => ! empty($this->clientId),
            'client_id_prefix' => substr($this->clientId, 0, 8) . '...',
            'secret_set'       => ! empty($this->clientSecret),
        ]);

        if ($isSandbox) {
            return new SandboxEnvironment($this->clientId, $this->clientSecret);
        }

        return new ProductionEnvironment($this->clientId, $this->clientSecret);
    }

    /**
     * Initialize properties.
     *
     * @return void
     */
    protected function initialize()
    {
        $this->clientId = $this->getConfigData('client_id') ?: '';

        $this->clientSecret = $this->getConfigData('client_secret') ?: '';
    }
}
