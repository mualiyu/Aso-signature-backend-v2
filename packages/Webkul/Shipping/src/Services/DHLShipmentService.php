<?php

namespace Webkul\Shipping\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Sales\Models\Order;

class DHLShipmentService
{
    /**
     * Create a shipment with DHL API.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @param  array  $shipmentData  Should contain 'items', 'source', etc.
     * @return array
     */
    public function createShipment(Order $order, array $shipmentData)
    {
        try {
            $sandboxMode = core()->getConfigData('sales.carriers.dhl.sandbox_mode');
            $apiKey = core()->getConfigData('sales.carriers.dhl.api_key');
            $apiSecret = core()->getConfigData('sales.carriers.dhl.api_secret');
            $accountNumber = core()->getConfigData('sales.carriers.dhl.account_number');

            if (empty($apiKey) || empty($apiSecret) || empty($accountNumber)) {
                throw new \Exception('DHL API credentials are not configured');
            }

            $baseUrl = $sandboxMode
                ? 'https://express.api.dhl.com/mydhlapi/test'
                : 'https://express.api.dhl.com/mydhlapi';

            // Get origin address from config
            $originAddress = $this->getOriginAddress();

            // Get shipping address from order
            $shippingAddress = $order->shipping_address;

            // Calculate total weight and dimensions from shipment items
            $totalWeight = 0;
            $dimensions = $this->calculatePackageDimensions($order, $shipmentData);

            // Ensure items array exists
            if (!isset($shipmentData['items']) || !is_array($shipmentData['items'])) {
                throw new \Exception('Shipment items are required');
            }

            foreach ($shipmentData['items'] as $itemId => $inventorySource) {
                $orderItem = $order->items()->find($itemId);
                if ($orderItem) {
                    $qty = $inventorySource[$shipmentData['source']] ?? 0;
                    $totalWeight += ($orderItem->weight ?? 0) * $qty;
                }
            }

            // Ensure minimum weight
            if ($totalWeight <= 0) {
                $totalWeight = 1.0; // Default 1kg
            }

            // Detect if shipping is international
            $isInternational = strtoupper($originAddress['country']) !== strtoupper($shippingAddress->country);

            // Calculate cart total for customs value (if international)
            $cartTotal = 0;
            if ($isInternational) {
                foreach ($order->items as $item) {
                    $cartTotal += $item->base_total;
                }
            }

            // Prepare DHL shipment request
            // Use next business day for pickup date (DHL requires future date)
            $nextBusinessDay = $this->getNextBusinessDay();
            // Format: '2010-02-11T17:10:09 GMT+01:00' (DHL shipment API requires timezone offset)
            $plannedDate = $nextBusinessDay->format('Y-m-d\TH:i:s') . ' GMT' . $nextBusinessDay->format('P');

            $requestBody = [
                'plannedShippingDateAndTime' => $plannedDate,
                'pickup' => [
                    'isRequested' => false,
                ],
                'productCode' => $shipmentData['dhl_product_code'] ?? 'P', // Default to Express Worldwide if not specified
                'accounts' => [
                    [
                        'number' => $accountNumber,
                        'typeCode' => 'shipper',
                    ],
                ],
                'customerDetails' => [
                    'shipperDetails' => [
                        'postalAddress' => [
                            'postalCode' => $originAddress['postcode'],
                            'cityName' => $originAddress['city'],
                            'countryCode' => $originAddress['country'],
                            'addressLine1' => $this->sanitizeAddressLine($originAddress['address1'] ?? $originAddress['city'] ?? 'Address'),
                            'addressLine2' => $this->sanitizeAddressLine($originAddress['address2'] ?? ''),
                        ],
                        'contactInformation' => [
                            'phone' => $this->sanitizePhone(core()->getConfigData('sales.carriers.dhl.origin_phone') ?? '1234567890'),
                            'fullName' => core()->getConfigData('sales.carriers.dhl.origin_company') ?? 'Shipper Company',
                            'companyName' => core()->getConfigData('sales.carriers.dhl.origin_company') ?? 'Shipper Company',
                        ],
                    ],
                    'receiverDetails' => [
                        'postalAddress' => [
                            'postalCode' => $shippingAddress->postcode,
                            'cityName' => $shippingAddress->city,
                            'countryCode' => $shippingAddress->country,
                            'addressLine1' => $this->sanitizeAddressLine($shippingAddress->address1 ?? $shippingAddress->city ?? 'Address'),
                            'addressLine2' => $this->sanitizeAddressLine($shippingAddress->address2 ?? ''),
                        ],
                        'contactInformation' => [
                            'phone' => $this->sanitizePhone($shippingAddress->phone ?? '1234567890'),
                            'fullName' => trim(($shippingAddress->first_name ?? 'Customer') . ' ' . ($shippingAddress->last_name ?? '')),
                            'companyName' => $shippingAddress->company_name ?? trim(($shippingAddress->first_name ?? 'Customer') . ' ' . ($shippingAddress->last_name ?? '')),
                        ],
                    ],
                ],
                'content' => [
                    'packages' => [
                        [
                            'typeCode' => '3BX', // Standard package
                            'weight' => max(0.1, round($totalWeight, 2)),
                            'dimensions' => [
                                'length' => max(1, round($dimensions['length'], 2)),
                                'width' => max(1, round($dimensions['width'], 2)),
                                'height' => max(1, round($dimensions['height'], 2)),
                            ],
                        ],
                    ],
                    'description' => 'Shipment', // Required field
                    'unitOfMeasurement' => 'metric', // Required field
                    'isCustomsDeclarable' => $isInternational,
                    'incoterm' => $isInternational ? 'DAP' : null, // Required for declarable shipments (DAP = Delivered At Place)
                    'declaredValue' => $isInternational && $cartTotal > 0 ? round($cartTotal, 2) : null,
                    'declaredValueCurrency' => $isInternational && $cartTotal > 0 ? core()->getBaseCurrencyCode() : null,
                ],
                'outputImageProperties' => [
                    'printerDPI' => 300,
                    // encodingFormat removed - let DHL use default format
                    'imageOptions' => [
                        [
                            'typeCode' => 'label',
                            'templateName' => 'ECOM26_84_001',
                        ],
                    ],
                ],
            ];

            // Remove null values from content
            if ($requestBody['content']['declaredValue'] === null) {
                unset($requestBody['content']['declaredValue']);
                unset($requestBody['content']['declaredValueCurrency']);
            }
            if ($requestBody['content']['incoterm'] === null) {
                unset($requestBody['content']['incoterm']);
            }

            $messageReference = uniqid();

            Log::info('DHL Shipment Creation Request', [
                'order_id' => $order->id,
                'url' => $baseUrl . '/shipments',
                'request_body_size' => strlen(json_encode($requestBody)),
            ]);

            // Increase timeout for shipment creation (can take longer than rates)
            // Note: connectTimeout handles DNS resolution timeout
            $response = Http::timeout(120) // 2 minutes request timeout
                ->connectTimeout(60) // 60 seconds for DNS resolution and connection
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Message-Reference' => $messageReference,
                    'Message-Reference-Date' => now()->format('Y-m-d\TH:i:s\Z'),
                ])
                ->withBasicAuth($apiKey, $apiSecret)
                ->post($baseUrl . '/shipments', $requestBody);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('DHL Shipment Created Successfully', [
                    'order_id' => $order->id,
                    'response' => $data,
                ]);

                // Extract tracking number and shipment details
                $trackingNumber = $data['shipmentTrackingNumber'] ?? null;
                $shipmentDetails = [
                    'tracking_number' => $trackingNumber,
                    'shipment_id' => $data['shipmentTrackingNumber'] ?? null,
                    'label' => $data['labelImage'][0]['graphicImage'] ?? null,
                    'label_format' => $data['labelImage'][0]['encodingFormat'] ?? null,
                    'dhl_response' => $data,
                ];

                return [
                    'success' => true,
                    'data' => $shipmentDetails,
                ];
            } else {
                $errorBody = json_decode($response->body(), true);
                $errorMessage = 'Unknown error';

                if (isset($errorBody['reasons']) && is_array($errorBody['reasons']) && !empty($errorBody['reasons'])) {
                    $errorMessage = $errorBody['reasons'][0]['msg'] ?? $errorBody['reasons'][0]['message'] ?? 'Unknown error';
                } elseif (isset($errorBody['message'])) {
                    $errorMessage = $errorBody['message'];
                } elseif (isset($errorBody['detail'])) {
                    $errorMessage = $errorBody['detail'];
                }

                // For 422 errors, extract additional details
                if ($response->status() == 422 && isset($errorBody['additionalDetails'])) {
                    $additionalDetails = is_array($errorBody['additionalDetails'])
                        ? implode(', ', $errorBody['additionalDetails'])
                        : $errorBody['additionalDetails'];
                    $errorMessage = $errorMessage . ' Details: ' . $additionalDetails;
                }

                Log::error('DHL Shipment Creation Error', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'error' => $errorMessage,
                    'full_response' => $response->body(),
                    'request_body' => $requestBody,
                ]);

                return [
                    'success' => false,
                    'error' => $errorMessage,
                ];
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Network/DNS connectivity issue
            Log::error('DHL Shipment Network Error', [
                'order_id' => $order->id ?? null,
                'message' => $e->getMessage(),
                'error' => 'Cannot reach DHL API - check internet connection and DNS',
            ]);

            return [
                'success' => false,
                'error' => 'Network Error: Cannot connect to DHL API. Please check your internet connection and DNS settings.',
            ];
        } catch (\Exception $e) {
            Log::error('DHL Shipment Creation Exception', [
                'order_id' => $order->id ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Check if it's a timeout error
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'timed out') !== false || strpos($errorMessage, 'Resolving timed out') !== false) {
                return [
                    'success' => false,
                    'error' => 'Connection Timeout: Unable to reach DHL API. This could be due to network issues or DNS problems. Please check your internet connection and try again.',
                ];
            }

            return [
                'success' => false,
                'error' => 'API Error: ' . $errorMessage,
            ];
        }
    }

    /**
     * Get origin address from configuration.
     *
     * @return array
     */
    protected function getOriginAddress()
    {
        return [
            'postcode' => core()->getConfigData('sales.carriers.dhl.origin_postcode') ?? '12345',
            'city' => core()->getConfigData('sales.carriers.dhl.origin_city') ?? 'Your City',
            'country' => core()->getConfigData('sales.carriers.dhl.origin_country') ?? 'US',
            'address1' => core()->getConfigData('sales.carriers.dhl.origin_address') ?? 'N/A',
            'address2' => 'N/A',
        ];
    }

    /**
     * Calculate package dimensions from order items.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @param  array  $shipmentData
     * @return array
     */
    protected function calculatePackageDimensions(Order $order, array $shipmentData)
    {
        $defaultLength = (float) (core()->getConfigData('sales.carriers.dhl.default_length') ?? 10);
        $defaultWidth = (float) (core()->getConfigData('sales.carriers.dhl.default_width') ?? 10);
        $defaultHeight = (float) (core()->getConfigData('sales.carriers.dhl.default_height') ?? 10);

        $maxLength = $defaultLength;
        $maxWidth = $defaultWidth;
        $maxHeight = $defaultHeight;

        // Ensure items array exists
        if (!isset($shipmentData['items']) || !is_array($shipmentData['items'])) {
            return [
                'length' => $maxLength,
                'width' => $maxWidth,
                'height' => $maxHeight,
            ];
        }

        foreach ($shipmentData['items'] as $itemId => $inventorySource) {
            $orderItem = $order->items()->find($itemId);
            if ($orderItem && $orderItem->product) {
                $product = $orderItem->product;
                $length = (float) ($product->length ?? $defaultLength);
                $width = (float) ($product->width ?? $defaultWidth);
                $height = (float) ($product->height ?? $defaultHeight);

                $maxLength = max($maxLength, $length);
                $maxWidth = max($maxWidth, $width);
                $maxHeight = max($maxHeight, $height);
            }
        }

        return [
            'length' => $maxLength,
            'width' => $maxWidth,
            'height' => $maxHeight,
        ];
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

    /**
     * Sanitize address line to ensure it matches DHL pattern requirements.
     * Pattern requires at least one non-whitespace character.
     *
     * @param  string  $addressLine
     * @return string
     */
    protected function sanitizeAddressLine($addressLine)
    {
        $addressLine = trim($addressLine ?? '');

        // If empty or only whitespace, use a default value
        if (empty($addressLine) || !preg_match('/\S/', $addressLine)) {
            return 'Address';
        }

        // Ensure it has at least one non-whitespace character (DHL requirement)
        return $addressLine;
    }

    /**
     * Sanitize phone number for DHL API.
     *
     * @param  string  $phone
     * @return string
     */
    protected function sanitizePhone($phone)
    {
        $phone = trim($phone ?? '');

        // Remove non-numeric characters except + for international format
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // If empty, use default
        if (empty($phone)) {
            return '1234567890';
        }

        return $phone;
    }
}

