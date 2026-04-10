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
        $rate = $this->getDHLRate($cart, $shippingAddress, $totalWeight, $dimensions);

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
                // Get dimensions from product attributes (cast to float to avoid string * int errors)
                $length = (float) ($product->length ?? 0);
                $width = (float) ($product->width ?? 0);
                $height = (float) ($product->height ?? 0);

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
     * @param  \Webkul\Checkout\Models\Cart  $cart
     * @param  \Webkul\Checkout\Models\CartAddress  $address
     * @param  float  $weight
     * @param  array  $dimensions
     * @return \Webkul\Checkout\Models\CartShippingRate|false
     */
    protected function getDHLRate($cart, $address, $weight, $dimensions)
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

            Log::info('DHL API config', [
                'api_credentials_configured' => true,
                'sandbox_mode'                 => (bool) $sandboxMode,
                'endpoint'                     => $baseUrl,
                'account_number_suffix'        => strlen($accountNumber) > 4 ? substr($accountNumber, -4) : null,
            ]);

            // Get origin address
            $originAddress = $this->getOriginAddress();

            // Prepare DHL API request for rates estimation
            $shipperLines = $this->buildRatesAddressLines(
                $originAddress['address1'] ?? '',
                $originAddress['address2'] ?? '',
                $originAddress['city'] ?? '',
                $originAddress['postcode'] ?? ''
            );

            $receiverLines = $this->buildRatesAddressLines(
                trim((string) ($address->address ?? $address->address1 ?? '')),
                trim((string) ($address->address2 ?? '')),
                $address->city ?? '',
                $address->postcode ?? ''
            );

            // Detect if shipping is international (different countries)
            $isInternational = strtoupper($originAddress['country']) !== strtoupper($address->country);

            Log::info('DHL Shipping Type', [
                'origin_country' => $originAddress['country'],
                'destination_country' => $address->country,
                'is_international' => $isInternational,
            ]);

            // Calculate cart total for customs value (if international)
            $cartTotal = 0;
            if ($isInternational && $cart) {
                foreach ($cart->items as $item) {
                    $cartTotal += $item->base_total;
                }
            }

            $requestBody = [
                'customerDetails' => [
                    'shipperDetails' => [
                        'postalCode'    => $originAddress['postcode'],
                        'cityName'      => $this->normalizeDhlCityNameForRates($originAddress['country'], $originAddress['postcode'] ?? '', $originAddress['city'] ?? ''),
                        'countryCode'   => $originAddress['country'],
                        'addressLine1'  => $shipperLines['line1'],
                        'addressLine2'  => $shipperLines['line2'],
                    ],
                    'receiverDetails' => [
                        'postalCode'    => $address->postcode,
                        'cityName'      => $this->normalizeDhlCityNameForRates($address->country, $address->postcode ?? '', $address->city ?? ''),
                        'countryCode'   => $address->country,
                        'addressLine1'  => $receiverLines['line1'],
                        'addressLine2'  => $receiverLines['line2'],
                    ],
                ],
                'accounts' => [
                    [
                        'number' => $accountNumber,
                        'typeCode' => 'shipper',
                    ],
                ],
                // Removed productCode - DHL will return all available products for the route
                // No productCode specified = DHL returns all available products
                'unitOfMeasurement' => 'metric',
                // Use next business day for pickup date (DHL requires future date)
                'plannedShippingDateAndTime' => $this->getNextBusinessDay()->format('Y-m-d\TH:i:s\Z'),
                'packages' => [
                    [
                        'weight' => max(0.1, round($weight, 2)), // Minimum 0.1 kg
                        'dimensions' => [
                            'length' => max(1, round($dimensions['length'], 2)), // Minimum 1 cm
                            'width' => max(1, round($dimensions['width'], 2)), // Minimum 1 cm
                            'height' => max(1, round($dimensions['height'], 2)), // Minimum 1 cm
                        ],
                    ],
                ],
                'isCustomsDeclarable' => $isInternational, // True for international, false for domestic
                'monetaryAmount' => $isInternational && $cartTotal > 0 ? [
                    [
                        'typeCode' => 'declaredValue',
                        'value' => round($cartTotal, 2), // Fix: DHL expects 'value' not 'amount'
                        'currency' => core()->getBaseCurrencyCode(), // Fix: DHL expects 'currency' not 'currencyCode'
                    ],
                ] : [],
                'getAdditionalInformation' => [], // Fix: Should be array, not boolean
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

                // Prefer API `detail` (validation paths); then reasons; then generic message
                $errorMessage = 'Unknown error';
                if (! empty($errorBody['detail'])) {
                    $errorMessage = $errorBody['detail'];
                } elseif (isset($errorBody['reasons']) && is_array($errorBody['reasons']) && ! empty($errorBody['reasons'])) {
                    $errorMessage = $errorBody['reasons'][0]['msg'] ?? $errorBody['reasons'][0]['message'] ?? 'Unknown error';
                } elseif (isset($errorBody['message'])) {
                    $errorMessage = $errorBody['message'];
                } elseif (isset($errorBody['error'])) {
                    $errorMessage = $errorBody['error'];
                }

                Log::error('DHL API Error', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'full_response' => $response->body(),
                    'parsed_response' => $errorBody,
                ]);

                // For 422 errors, provide more specific guidance
                if ($response->status() == 422) {
                    $errorMessage = 'Validation Error: ' . $errorMessage . ' (Check postal codes, country codes, or package dimensions)';
                }

                return $this->getTestRateWithError($errorMessage);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Network/DNS connectivity issue
            Log::error('DHL Network Error', [
                'message' => $e->getMessage(),
                'error' => 'Cannot reach DHL API - check internet connection and DNS',
            ]);

            if ($this->getConfigData('fallback_enabled')) {
                return $this->getFallbackRate();
            }

            return $this->getTestRateWithError('Network Error: Cannot connect to DHL API. Please check your internet connection.');
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
     * Parse DHL API response and return rate(s).
     *
     * @param  array  $response
     * @return \Webkul\Checkout\Models\CartShippingRate|array
     */
    protected function parseDHLResponse($response)
    {
        if (!isset($response['products']) || !is_array($response['products']) || empty($response['products'])) {
            return false;
        }

        $rates = [];

        foreach ($response['products'] as $product) {
            $cartShippingRate = new CartShippingRate();

            $cartShippingRate->carrier = $this->getCode();
            $cartShippingRate->carrier_title = $this->getConfigData('title');
            $cartShippingRate->method = $this->getCode() . '_' . ($product['productCode'] ?? 'express');
            $cartShippingRate->method_title = $product['productName'] ?? 'DHL Express';

            $deliveryCaps = $product['deliveryCapabilities'] ?? [];
            $estimatedDeliveryDate = $deliveryCaps['estimatedDeliveryDateAndTime']
                ?? ($product['estimatedDeliveryDateAndTime']['estimatedDeliveryDate'] ?? '');
            $deliveryTime = $product['deliveryCommitment']['deliveryTime'] ?? '';

            $cartShippingRate->method_description = 'Delivery: ' . ($estimatedDeliveryDate ?: $deliveryTime ?: 'Standard delivery');

            // Prefer BILLC (billing) from totalPrice; else first row
            $billPrice = 0;
            $billCurrency = null;
            if (isset($product['totalPrice']) && is_array($product['totalPrice'])) {
                foreach ($product['totalPrice'] as $priceEntry) {
                    if (($priceEntry['currencyType'] ?? '') === 'BILLC' && isset($priceEntry['price'])) {
                        $billPrice = (float) $priceEntry['price'];
                        $billCurrency = $priceEntry['priceCurrency'] ?? null;
                        break;
                    }
                }
                if ($billPrice <= 0 && isset($product['totalPrice'][0]['price'])) {
                    $billPrice = (float) $product['totalPrice'][0]['price'];
                    $billCurrency = $product['totalPrice'][0]['priceCurrency'] ?? null;
                }
            }

            $userCurrencyCode = core()->getCurrentCurrencyCode();
            $baseCurrencyCode = core()->getBaseCurrencyCode();

            if ($billCurrency && strtoupper($baseCurrencyCode) === strtoupper($billCurrency)) {
                $basePrice = $billPrice;
            } elseif ($billCurrency) {
                $basePrice = core()->convertToBasePrice($billPrice, $billCurrency);
            } else {
                $basePrice = $billPrice;
            }

            // Convert base price to user's selected currency
            // If user currency is same as base, no conversion needed
            if (strtoupper($userCurrencyCode) === strtoupper($baseCurrencyCode)) {
                $convertedPrice = $basePrice;
            } else {
                // Convert from base currency to user's currency
                $convertedPrice = core()->convertPrice($basePrice, $userCurrencyCode);
            }

            $cartShippingRate->base_price = round($basePrice, 2);
            $cartShippingRate->price = round($convertedPrice, 2);

            $rates[] = $cartShippingRate;

            Log::info('DHL Rate Conversion', [
                'product'         => $product['productName'] ?? 'Unknown',
                'bill_price'      => $billPrice,
                'bill_currency'   => $billCurrency,
                'base_currency'   => $baseCurrencyCode,
                'base_price'      => $basePrice,
                'user_currency'   => $userCurrencyCode,
                'converted_price' => $convertedPrice,
            ]);
        }

        // Return single rate or array of rates
        return count($rates) === 1 ? $rates[0] : $rates;
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

        $cartShippingRate->base_price = 0;
        $cartShippingRate->price = 0;

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

        $cartShippingRate->base_price = 0;
        $cartShippingRate->price = 0;

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

        // Use fallback rate directly without conversion
        $fallbackPrice = (float) ($this->getConfigData('fallback_rate') ?: 0);

        $cartShippingRate->base_price = round($fallbackPrice, 2);
        $cartShippingRate->price = round($fallbackPrice, 2);

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
            'postcode'  => $this->getConfigData('origin_postcode') ?? '12345',
            'city'      => $this->getConfigData('origin_city') ?? 'Your City',
            'country'   => $this->getConfigData('origin_country') ?? 'US',
            'address1'  => $this->getConfigData('origin_address') ?? '',
            'address2'  => $this->getConfigData('origin_address_line_2') ?? '',
        ];
    }

    /**
     * DHL location validation expects a gazetteer city name. In Nigeria, checkout often stores
     * an area council (e.g. AMAC) while postcodes 900xxx belong to Abuja FCT — DHL returns 420505 otherwise.
     *
     * @see https://www.dhl.com/ng-en/home/local-information/postal-codes.html (FCT uses 900xxx)
     */
    protected function normalizeDhlCityNameForRates(string $countryCode, string $postcode, string $city): string
    {
        if (strtoupper(trim($countryCode)) !== 'NG') {
            return $city;
        }

        $digits = preg_replace('/\D/', '', (string) $postcode);
        if (strlen($digits) !== 6) {
            return $city;
        }

        // Federal Capital Territory — DHL expects "Abuja" as city, not district names (AMAC, Bwari, etc.)
        if (preg_match('/^90\d{4}$/', $digits)) {
            return 'Abuja';
        }

        return $city;
    }

    /**
     * Build address lines for DHL /rates (non-empty lines required; avoid placeholder "N/A").
     *
     * @return array{line1: string, line2: string}
     */
    protected function buildRatesAddressLines(string $line1, string $line2, string $city, string $postcode): array
    {
        $line1 = trim($line1);
        $line2 = trim($line2);

        if ($line1 === '') {
            $line1 = $city !== '' ? $city : ($postcode !== '' ? $postcode : 'Address');
        }

        if ($line2 === '') {
            if ($city !== '' && strcasecmp($line1, $city) !== 0) {
                $line2 = $city;
            } elseif ($postcode !== '') {
                $line2 = $postcode;
            } else {
                $line2 = $city !== '' ? $city : $line1;
            }
        }

        return [
            'line1' => $this->truncateDhlAddressLine($this->sanitizeAddressLine($line1)),
            'line2' => $this->truncateDhlAddressLine($this->sanitizeAddressLine($line2)),
        ];
    }

    /**
     * MyDHL API: postalAddress addressLine1/2 maxLength 45 (per Express API schema).
     */
    protected function truncateDhlAddressLine(string $addressLine, int $maxLength = 45): string
    {
        if (function_exists('mb_strlen') && mb_strlen($addressLine, 'UTF-8') > $maxLength) {
            return mb_substr($addressLine, 0, $maxLength, 'UTF-8');
        }

        return strlen($addressLine) > $maxLength ? substr($addressLine, 0, $maxLength) : $addressLine;
    }

    /**
     * Ensure at least one non-whitespace character (DHL pattern).
     */
    protected function sanitizeAddressLine(string $addressLine): string
    {
        $addressLine = trim($addressLine);

        if ($addressLine === '' || ! preg_match('/\S/', $addressLine)) {
            return 'Address';
        }

        return $addressLine;
    }

    /**
     * Get next business day for DHL pickup date.
     * DHL requires a future date for pickup.
     *
     * @return \Carbon\Carbon
     */
    protected function getNextBusinessDay()
    {
        $date = now();

        // If it's after 3 PM, use next day
        if ($date->hour >= 15) {
            $date = $date->addDay();
        }

        // Skip weekends (Saturday = 6, Sunday = 0)
        while ($date->dayOfWeek === 0 || $date->dayOfWeek === 6) {
            $date = $date->addDay();
        }

        // Set to 10 AM for pickup time
        return $date->setTime(10, 0, 0);
    }
}

