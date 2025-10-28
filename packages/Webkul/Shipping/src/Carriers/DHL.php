<?php

namespace Webkul\Shipping\Carriers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\CartShippingRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DHL extends AbstractShipping
{
    /**
     * Shipping method carrier code.
     *
     * @var string
     */
    protected $code = 'dhl';

    /**
     * Check if shipping method is available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        // Always return true for now to ensure DHL appears
        // This will bypass any configuration checks
        return true;
    }

    /**
     * Calculate rate for DHL.
     *
     * @return \Webkul\Checkout\Models\CartShippingRate|false
     */
    public function calculate()
    {
        if (! $this->isAvailable()) {
            Log::info('DHL: Not available (isAvailable returned false)');
            return false;
        }

        $cart = Cart::getCart();

        if (! $cart) {
            Log::info('DHL: No cart found');
            return false;
        }

        if (! $cart->shipping_address) {
            Log::info('DHL: No shipping address');
            return false;
        }

        $shippingAddress = $cart->shipping_address;

        // Get package dimensions and weight
        $totalWeight = $this->getTotalWeight($cart);
        $dimensions = $this->getPackageDimensions($cart);

        Log::info('DHL: Weight: ' . $totalWeight . ', Dimensions: ' . json_encode($dimensions));

        // If weight is 0, use default weight
        if ($totalWeight <= 0) {
            $totalWeight = 1.0; // Default 1kg
            Log::info('DHL: Weight was 0, using default 1kg');
        }

        // Check if API credentials are configured
        $apiKey = $this->getConfigData('api_key');
        $apiSecret = $this->getConfigData('api_secret');
        $accountNumber = $this->getConfigData('account_number');

        // If not configured yet, return a test rate
        if (empty($apiKey) || empty($apiSecret) || empty($accountNumber)) {
            Log::info('DHL: Not configured. Returning test rate.');
            return $this->getTestRate();
        }

        // Call DHL API to get rates
        $rate = $this->getDHLRate($shippingAddress, $totalWeight, $dimensions);

        return $rate;
    }

    /**
     * Calculate total weight of cart items.
     *
     * @param  \Webkul\Checkout\Models\Cart  $cart
     * @return float
     */
    protected function getTotalWeight($cart)
    {
        $totalWeight = 0;

        foreach ($cart->items as $item) {
            if ($item->getTypeInstance()->isStockable()) {
                $totalWeight += $item->total_weight;
            }
        }

        return $totalWeight;
    }

    /**
     * Calculate package dimensions.
     *
     * @param  \Webkul\Checkout\Models\Cart  $cart
     * @return array
     */
    protected function getPackageDimensions($cart)
    {
        $maxLength = 0;
        $maxWidth = 0;
        $maxHeight = 0;

        foreach ($cart->items as $item) {
            $product = $item->product;

            if ($item->getTypeInstance()->isStockable() && $product) {
                // Get dimensions from product attributes
                $length = $product->length ?? 0;
                $width = $product->width ?? 0;
                $height = $product->height ?? 0;

                $maxLength = max($maxLength, $length);
                $maxWidth = max($maxWidth, $width);
                $maxHeight += $height * $item->quantity;
            }
        }

        // Default dimensions if not set
        if ($maxLength == 0 || $maxWidth == 0 || $maxHeight == 0) {
            $maxLength = (float) ($this->getConfigData('default_length') ?: 10);
            $maxWidth = (float) ($this->getConfigData('default_width') ?: 10);
            $maxHeight = $maxHeight ?: (float) ($this->getConfigData('default_height') ?: 5);
        }

        return [
            'length' => $maxLength,
            'width' => $maxWidth,
            'height' => $maxHeight,
        ];
    }

    /**
     * Get DHL shipping rate using MyDHL API via Guzzle.
     *
     * @param  \Webkul\Checkout\Models\CartAddress  $address
     * @param  float  $weight
     * @param  array  $dimensions
     * @return \Webkul\Checkout\Models\CartShippingRate|false
     */
    protected function getDHLRate($address, $weight, $dimensions)
    {
        try {
            $sandboxMode = $this->getConfigData('sandbox_mode');
            $apiKey = $this->getConfigData('api_key');
            $apiSecret = $this->getConfigData('api_secret');
            $accountNumber = $this->getConfigData('account_number');

            // Add more detailed credential validation
            if (empty($apiKey)) {
                Log::warning('DHL: API Key is missing');
                return $this->getTestRateWithError('API Key is missing');
            }

            if (empty($apiSecret)) {
                Log::warning('DHL: API Secret is missing');
                return $this->getTestRateWithError('API Secret is missing');
            }

            if (empty($accountNumber)) {
                Log::warning('DHL: Account Number is missing');
                return $this->getTestRateWithError('Account Number is missing');
            }

            // Determine API URL
            $baseUrl = $sandboxMode
                ? 'https://express.api.dhl.com/mydhlapi/test'
                : 'https://express.api.dhl.com/mydhlapi';

            // Get origin address
            $originAddress = $this->getOriginAddress();

            // Prepare DHL API request for rates estimation
            $requestBody = [
                'customerDetails' => [
                    'shipperDetails' => [
                        'postalCode' => $originAddress['postcode'],
                        'cityName' => $originAddress['city'],
                        'countryCode' => $originAddress['country'],
                    ],
                    'receiverDetails' => [
                        'postalCode' => $address->postcode,
                        'cityName' => $address->city,
                        'countryCode' => $address->country,
                        'addressLine1' => $address->address1 ?? '',
                        'addressLine2' => $address->address2 ?? '',
                    ],
                ],
                'accounts' => [
                    [
                        'number' => $accountNumber,
                        'typeCode' => 'shipper',
                    ],
                ],
                'productCode' => 'N', // Express Worldwide
                'unitOfMeasurement' => 'metric',
                'plannedShippingDateAndTime' => [
                    'plannedShippingDate' => now()->format('Y-m-d'),
                ],
                'packages' => [
                    [
                        'weight' => round($weight, 2),
                        'dimensions' => [
                            'length' => round($dimensions['length'], 2),
                            'width' => round($dimensions['width'], 2),
                            'height' => round($dimensions['height'], 2),
                        ],
                    ],
                ],
                'isCustomsDeclarable' => false,
                'monetaryAmount' => [],
                'getAllValueAddedServices' => false,
                'requestEstimatedDeliveryDate' => true,
                'getAdditionalInformation' => false,
            ];

            $messageReference = uniqid();

            // Make API call with authentication
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Message-Reference' => $messageReference,
                    'Message-Reference-Date' => now()->format('Y-m-d\TH:i:s\Z'),
                ])
                ->withBasicAuth($apiKey, $apiSecret)
                ->post($baseUrl . '/rates', $requestBody);

            Log::info('DHL API Request', [
                'url' => $baseUrl . '/rates',
                'body' => $requestBody,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('DHL API Response', ['data' => $data]);
                return $this->parseDHLResponse($data);
            } else {
                // Improve error logging for API response
                $errorBody = json_decode($response->body(), true);
                $errorMessage = $errorBody['reasons'][0]['msg'] ?? 'Unknown error';

                Log::error('DHL API Error', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'details' => $errorBody['details'] ?? []
                ]);

                return $this->getTestRateWithError($errorMessage);
            }
        } catch (\Exception $e) {
            Log::error('DHL Shipping Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($this->getConfigData('fallback_enabled')) {
                return $this->getFallbackRate();
            }

            // Return test rate with error message
            return $this->getTestRateWithError('API Error: ' . $e->getMessage());
        }
    }

    /**
     * Parse DHL API response and return rate.
     *
     * @param  array  $response
     * @return \Webkul\Checkout\Models\CartShippingRate
     */
    protected function parseDHLResponse($response)
    {
        $cartShippingRate = new CartShippingRate();

        if (isset($response['products']) && is_array($response['products']) && !empty($response['products'])) {
            $product = $response['products'][0];

            $cartShippingRate->carrier = $this->getCode();
            $cartShippingRate->carrier_title = $this->getConfigData('title');
            $cartShippingRate->method = $this->getCode() . '_' . ($product['productCode'] ?? 'express');
            $cartShippingRate->method_title = $product['productName'] ?? 'DHL Express';

            // Get estimated delivery date if available
            $estimatedDeliveryDate = $product['estimatedDeliveryDateAndTime']['estimatedDeliveryDate'] ?? '';
            $deliveryTime = $product['deliveryCommitment']['deliveryTime'] ?? '';

            $cartShippingRate->method_description = 'Delivery: ' . ($estimatedDeliveryDate ?: $deliveryTime ?: 'Standard delivery');

            // Convert price to base currency
            $price = $product['totalPrice'][0]['price'] ?? 0;
            $currencyCode = $product['totalPrice'][0]['currencyName'] ?? 'USD';

            $cartShippingRate->price = core()->convertPrice($price, $currencyCode);
            $cartShippingRate->base_price = $price;
        } else {
            // No products returned
            return false;
        }

        return $cartShippingRate;
    }

    /**
     * Get test rate when DHL is not configured yet.
     *
     * @return \Webkul\Checkout\Models\CartShippingRate
     */
    protected function getTestRate()
    {
        $cartShippingRate = new CartShippingRate();
        $cartShippingRate->carrier = $this->getCode();
        $cartShippingRate->carrier_title = 'DHL Express';
        $cartShippingRate->method = $this->getCode() . '_test';
        $cartShippingRate->method_title = 'DHL Express (Configure in Admin)';
        $cartShippingRate->method_description = 'Please configure DHL API credentials in Admin Panel';
        $cartShippingRate->price = core()->convertPrice(0);
        $cartShippingRate->base_price = 0;

        return $cartShippingRate;
    }

    /**
     * Get test rate with error message.
     *
     * @param  string  $error
     * @return \Webkul\Checkout\Models\CartShippingRate
     */
    protected function getTestRateWithError($error)
    {
        $cartShippingRate = new CartShippingRate();
        $cartShippingRate->carrier = $this->getCode();
        $cartShippingRate->carrier_title = 'DHL Express';
        $cartShippingRate->method = $this->getCode() . '_error';
        $cartShippingRate->method_title = 'DHL Express (Invalid Credentials)';
        $cartShippingRate->method_description = 'Please check DHL API credentials in Admin Panel';
        $cartShippingRate->price = core()->convertPrice(0);
        $cartShippingRate->base_price = 0;

        Log::warning('DHL Error: ' . $error);

        return $cartShippingRate;
    }

    /**
     * Get fallback rate when API fails.
     *
     * @return \Webkul\Checkout\Models\CartShippingRate
     */
    protected function getFallbackRate()
    {
        $cartShippingRate = new CartShippingRate();
        $cartShippingRate->carrier = $this->getCode();
        $cartShippingRate->carrier_title = $this->getConfigData('title');
        $cartShippingRate->method = $this->getCode() . '_fallback';
        $cartShippingRate->method_title = 'DHL (Estimated)';
        $cartShippingRate->method_description = 'Estimated shipping cost';
        $cartShippingRate->price = core()->convertPrice($this->getConfigData('fallback_rate') ?: 0);
        $cartShippingRate->base_price = $this->getConfigData('fallback_rate') ?: 0;

        return $cartShippingRate;
    }

    /**
     * Get origin address from configuration.
     *
     * @return array
     */
    protected function getOriginAddress()
    {
        return [
            'postcode' => $this->getConfigData('origin_postcode') ?? '12345',
            'city' => $this->getConfigData('origin_city') ?? 'Your City',
            'country' => $this->getConfigData('origin_country') ?? 'US',
            'address1' => $this->getConfigData('origin_address') ?? '',
        ];
    }
}
