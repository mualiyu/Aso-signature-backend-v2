<?php

namespace Webkul\Shipping\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Webkul\Product\Models\ProductFlat;
use Webkul\Sales\Models\Order;

class DHLShipmentService
{
    /**
     * Create a shipment with DHL API.
     *
     * @param  array  $shipmentData  Should contain 'items', 'source', etc.
     * @return array{success: bool, data?: array, error?: string}
     */
    public function createShipment(Order $order, array $shipmentData)
    {
        try {
            $sandboxMode = $this->dhlConfig('sandbox_mode');
            $apiKey = $this->dhlConfig('api_key');
            $apiSecret = $this->dhlConfig('api_secret');
            $accountNumber = $this->dhlConfig('account_number');

            if (empty($apiKey) || empty($apiSecret) || empty($accountNumber)) {
                throw new \Exception('DHL API credentials are not configured');
            }

            $baseUrl = $sandboxMode
                ? 'https://express.api.dhl.com/mydhlapi/test'
                : 'https://express.api.dhl.com/mydhlapi';

            $originAddress = $this->getOriginAddress();
            $shippingAddress = $order->shipping_address;

            $dimensions = $this->calculatePackageDimensions($order, $shipmentData);

            if (! isset($shipmentData['items']) || ! is_array($shipmentData['items'])) {
                throw new \Exception('Shipment items are required');
            }

            $totalWeight = 0;
            foreach ($shipmentData['items'] as $itemId => $inventorySource) {
                $orderItem = $order->items()->find($itemId);
                if ($orderItem) {
                    $qty = $inventorySource[$shipmentData['source']] ?? 0;
                    $totalWeight += ($orderItem->weight ?? 0) * $qty;
                }
            }

            if ($totalWeight <= 0) {
                $totalWeight = 1.0;
            }

            $isInternational = strtoupper($originAddress['country']) !== strtoupper($shippingAddress->country);

            $nextBusinessDay = $this->getNextBusinessDay();
            // Shipments API requires literal format: 2010-02-11T17:10:09 GMT+01:00 (not ISO Z)
            $plannedDate = $nextBusinessDay->format('Y-m-d\TH:i:s').' GMT'.$nextBusinessDay->format('P');

            $productCode = $shipmentData['dhl_product_code'] ?? null;
            if (! $productCode && $order->shipping_method && str_starts_with($order->shipping_method, 'dhl_')) {
                $productCode = substr($order->shipping_method, 4) ?: null;
            }
            if (! $isInternational) {
                $productCode = 'N';
            } elseif (! $productCode) {
                $productCode = 'P';
            }

            $shipperEmail = $this->dhlConfig('origin_email') ?: $order->customer_email ?: 'noreply@example.com';
            $receiverEmail = $order->customer_email ?: $shipperEmail;

            // $incoterm = $this->dhlConfig('incoterm', 'DAP');

            // Set DAP Incoterm for the destination city/country, not the origin.
            $destinationCity = $shippingAddress->city ?? '';
            $destinationCountry = $shippingAddress->country ?? '';
            // DHL paperwork and electronic documents require just the Incoterm code (e.g., "DAP") for "Terms of Trade"
            // and the place-of-incoterm (city) separately (e.g., "Abuja"). Do not combine city/country directly in the Incoterm value.
            $incoterm = $this->dhlConfig('incoterm', 'DAP');
            $placeOfIncoterm = trim($shippingAddress->city ?? '');



            $shipperLines = $this->buildRatesAddressLines(
                $originAddress['address1'] ?? '',
                $originAddress['address2'] ?? '',
                $originAddress['city'] ?? '',
                $originAddress['postcode'] ?? ''
            );

            $receiverPostal = $this->buildReceiverPostalAddressLines($shippingAddress);

            $receiverPersonName = trim(($shippingAddress->first_name ?? '').' '.($shippingAddress->last_name ?? ''));
            if ($receiverPersonName === '') {
                $receiverPersonName = 'Customer';
            }

            $receiverPhoneE164 = $this->formatPhoneForDhl(
                $this->resolveReceiverPhoneRaw($order, $shippingAddress),
                $shippingAddress->country ?? ''
            );

            $receiverPostalAddress = [
                'postalCode'    => $shippingAddress->postcode,
                'cityName'      => $this->normalizeDhlCityNameForRates($shippingAddress->country, $shippingAddress->postcode ?? '', $shippingAddress->city ?? ''),
                'countryCode'   => $shippingAddress->country,
                'addressLine1'  => $receiverPostal['line1'],
                'addressLine2'  => $receiverPostal['line2'],
            ];
            if ($receiverPostal['line3'] !== '') {
                $receiverPostalAddress['addressLine3'] = $receiverPostal['line3'];
            }

            $requestBody = [
                'plannedShippingDateAndTime' => $plannedDate,
                'pickup'                     => [
                    'isRequested' => false,
                ],
                'productCode'                => $productCode,
                'accounts'                   => [
                    [
                        'number'   => $accountNumber,
                        'typeCode' => 'shipper',
                    ],
                ],
                'customerDetails'            => [
                    'shipperDetails'   => [
                        'typeCode'             => 'business',
                        'postalAddress'        => [
                            'postalCode'    => $originAddress['postcode'],
                            'cityName'      => $this->normalizeDhlCityNameForRates($originAddress['country'], $originAddress['postcode'] ?? '', $originAddress['city'] ?? ''),
                            'countryCode'   => $originAddress['country'],
                            'addressLine1'  => $shipperLines['line1'],
                            'addressLine2'  => $shipperLines['line2'],
                        ],
                        'contactInformation'   => [
                            'phone'       => $this->formatPhoneForDhl(
                                $this->dhlConfig('origin_phone') ?? '',
                                $originAddress['country'] ?? ''
                            ),
                            'email'       => $shipperEmail,
                            'fullName'    => $this->dhlConfig('origin_company') ?: 'Shipper',
                            'companyName' => $this->dhlConfig('origin_company') ?: 'Shipper',
                        ],
                    ],
                    'receiverDetails'  => [
                        'typeCode'             => 'business',
                        'postalAddress'        => $receiverPostalAddress,
                        'contactInformation'   => $this->buildReceiverContactInformation(
                            $shippingAddress,
                            $receiverEmail,
                            $receiverPersonName,
                            $receiverPhoneE164
                        ),
                    ],
                ],
                'content'                    => [
                    'packages'             => [
                        [
                            'weight'     => max(0.1, round($totalWeight, 2)),
                            'dimensions' => [
                                'length' => max(1, round($dimensions['length'], 2)),
                                'width'  => max(1, round($dimensions['width'], 2)),
                                'height' => max(1, round($dimensions['height'], 2)),
                            ],
                        ],
                    ],
                    'description'          => 'Shipment',
                    'unitOfMeasurement'    => 'metric',
                    'isCustomsDeclarable'  => $isInternational,
                ],
                'outputImageProperties'      => $this->buildOutputImageProperties($isInternational),
            ];

            if ($isInternational) {
                $exportDeclaration = $this->buildExportDeclaration($order, $shipmentData, $originAddress, $incoterm, $placeOfIncoterm);

                if (empty($exportDeclaration['declaration']['lineItems'])) {
                    throw new \Exception('Cannot create international DHL shipment: no line items for customs.');
                }

                $requestBody['content']['exportDeclaration'] = $exportDeclaration['declaration'];
                $requestBody['content']['declaredValue'] = $exportDeclaration['declared_total'];
                $requestBody['content']['declaredValueCurrency'] = core()->getBaseCurrencyCode();
                $requestBody['content']['incoterm'] = $incoterm;

                // $requestBody['content']['incoterm'] = $receiverPostalAddress['cityName'];
                // $requestBody['content']['incoterm'] = $incoterm;
            } else {
                $requestBody['content']['incoterm'] = $incoterm;
            }

            $messageReference = uniqid('', true);

            Log::info('DHL Shipment Creation Request', [
                'order_id' => $order->id,
                'url'      => $baseUrl.'/shipments',
                'intl'     => $isInternational,
                'product'  => $productCode,
            ]);

            $response = Http::timeout(120)
                ->connectTimeout(60)
                ->withHeaders([
                    'Content-Type'           => 'application/json',
                    'Accept'                 => 'application/json',
                    'Message-Reference'      => $messageReference,
                    'Message-Reference-Date' => now()->format('Y-m-d\TH:i:s\Z'),
                ])
                ->withBasicAuth($apiKey, $apiSecret)
                ->post($baseUrl.'/shipments', $requestBody);

            if ($response->successful()) {
                $data = $response->json();

                $trackingNumber = $data['shipmentTrackingNumber'] ?? null;
                $pdfBase64 = $this->extractPdfBase64($data);

                $documentsPath = null;
                if ($pdfBase64) {
                    try {
                        $documentsPath = $this->storeShipmentPdf($order, $pdfBase64);
                    } catch (\Throwable $e) {
                        Log::error('DHL: failed to store PDF', [
                            'order_id' => $order->id,
                            'message'  => $e->getMessage(),
                        ]);
                    }
                } else {
                    Log::warning('DHL Shipment: no PDF payload in response', [
                        'order_id' => $order->id,
                        'keys'     => array_keys($data ?? []),
                    ]);
                }

                Log::info('DHL Shipment Created Successfully', [
                    'order_id'         => $order->id,
                    'tracking'         => $trackingNumber,
                    'documents_stored' => (bool) $documentsPath,
                ]);

                return [
                    'success' => true,
                    'data'    => [
                        'tracking_number'    => $trackingNumber,
                        'shipment_id'        => $trackingNumber,
                        'dhl_documents_path' => $documentsPath,
                        'dhl_response'       => $data,
                    ],
                ];
            }

            $errorBody = json_decode($response->body(), true);
            $errorMessage = 'Unknown error';

            if (! empty($errorBody['detail'])) {
                $errorMessage = $errorBody['detail'];
            } elseif (isset($errorBody['reasons']) && is_array($errorBody['reasons']) && ! empty($errorBody['reasons'])) {
                $errorMessage = $errorBody['reasons'][0]['msg'] ?? $errorBody['reasons'][0]['message'] ?? 'Unknown error';
            } elseif (isset($errorBody['message'])) {
                $errorMessage = $errorBody['message'];
            }

            if ($response->status() == 422 && isset($errorBody['additionalDetails'])) {
                $additionalDetails = is_array($errorBody['additionalDetails'])
                    ? implode(', ', $errorBody['additionalDetails'])
                    : $errorBody['additionalDetails'];
                $errorMessage = $errorMessage.' Details: '.$additionalDetails;
            }

            Log::error('DHL Shipment Creation Error', [
                'order_id' => $order->id,
                'status'   => $response->status(),
                'error'    => $errorMessage,
                'body'     => $response->body(),
            ]);

            return [
                'success' => false,
                'error'   => $errorMessage,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('DHL Shipment Network Error', [
                'order_id' => $order->id ?? null,
                'message'  => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => 'Network Error: Cannot connect to DHL API. Please check your internet connection and DNS settings.',
            ];
        } catch (\Exception $e) {
            Log::error('DHL Shipment Creation Exception', [
                'order_id' => $order->id ?? null,
                'message'  => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            $errorMessage = $e->getMessage();
            if (stripos($errorMessage, 'timeout') !== false || stripos($errorMessage, 'Resolving timed out') !== false) {
                return [
                    'success' => false,
                    'error'   => 'Connection Timeout: Unable to reach DHL API. Please check your internet connection and try again.',
                ];
            }

            return [
                'success' => false,
                'error'   => 'API Error: '.$errorMessage,
            ];
        }
    }

    protected function dhlConfig(string $key, $default = null)
    {
        return core()->getConfigData('sales.carriers.dhl.'.$key) ?? $default;
    }

    /**
     * @return array{declaration: array, declared_total: float}
     */
    protected function buildExportDeclaration(Order $order, array $shipmentData, array $originAddress, string $incoterm, string $placeOfIncoterm): array
    {
        $lineItems = [];
        $declaredTotal = 0;
        $lineNumber = 1;
        $sourceId = $shipmentData['source'];

        foreach ($shipmentData['items'] as $itemId => $inventorySource) {
            $qty = (float) ($inventorySource[$sourceId] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $orderItem = $order->items()->find($itemId);
            if (! $orderItem) {
                continue;
            }

            $product = $orderItem->product;
            $linePrice = round((float) $orderItem->base_price * $qty, 2);
            $declaredTotal += $linePrice;

            $weightPerUnit = (float) ($orderItem->weight ?? 0.1);
            $gross = max(0.01, round($weightPerUnit * $qty, 3));

            $description = $this->buildDhlExportLineDescription($order, $orderItem, $product);

            $commodityCodes = $this->commodityCodesForProduct($product);

            $lineItems[] = [
                'number'             => $lineNumber,
                'description'        => $description,
                'price'              => $linePrice,
                'quantity'           => [
                    'unitOfMeasurement' => 'PCS',
                    'value'               => $qty,
                ],
                'weight'             => [
                    'netValue'   => $gross,
                    'grossValue' => $gross,
                ],
                'commodityCodes'     => $commodityCodes,
                'exportReasonType'   => 'permanent',
                'manufacturerCountry'=> $originAddress['country'],
            ];

            $lineNumber++;
        }

        $invoiceDate = $order->created_at
            ? $order->created_at->format('Y-m-d')
            : now()->format('Y-m-d');

        $declaration = [
            'lineItems'        => $lineItems,
            'exportReason'     => 'Permanent',
            'exportReasonType' => 'permanent',
            'shipmentType'     => 'commercial',
            'invoice'          => [
                'number' => (string) $order->increment_id,
                'date'   => $invoiceDate,
            ],
            'placeOfIncoterm'  => $placeOfIncoterm,
        ];

        return [
            'declaration'    => $declaration,
            'declared_total' => round($declaredTotal, 2),
        ];
    }

    /**
     * Customs line description: order line name plus plain-text short description (DHL max 512 chars).
     */
    protected function buildDhlExportLineDescription(Order $order, $orderItem, $product): string
    {
        $name = trim((string) ($orderItem->name ?? ''));
        $short = $product ? $this->plainTextShortDescriptionForProduct($order, $product) : '';

        if ($short === '') {
            $desc = $name;
        } else {
            $desc = $name === '' ? $short : $name.' — '.$short;
        }

        $desc = trim($desc);
        if ($desc === '') {
            $desc = 'Item';
        }

        if (strlen($desc) > 512) {
            $desc = substr($desc, 0, 512);
        }

        return $desc;
    }

    /**
     * Resolve short_description from product_flat (variant then parent), then from attribute_values.
     */
    protected function plainTextShortDescriptionForProduct(Order $order, $product): string
    {
        if (! $product) {
            return '';
        }

        $order->loadMissing('channel.default_locale');

        $channel = $order->channel;
        $channelCode = $channel?->code ?? core()->getDefaultChannelCode();
        $localeCode = $channel?->default_locale?->code ?? core()->getDefaultLocaleCodeFromDefaultChannel();

        $candidateIds = [(int) $product->id];
        if (! empty($product->parent_id)) {
            $candidateIds[] = (int) $product->parent_id;
        }

        foreach ($candidateIds as $pid) {
            $raw = ProductFlat::query()
                ->where('channel', $channelCode)
                ->where('locale', $localeCode)
                ->where('product_id', $pid)
                ->value('short_description');

            $plain = $this->flattenHtmlTextToSingleLine($raw);
            if ($plain !== '') {
                return $plain;
            }
        }

        foreach ($candidateIds as $pid) {
            $rows = ProductFlat::query()
                ->where('channel', $channelCode)
                ->where('product_id', $pid)
                ->whereNotNull('short_description')
                ->where('short_description', '!=', '')
                ->get(['locale', 'short_description']);

            $row = $rows->firstWhere('locale', $localeCode) ?? $rows->first();
            if ($row) {
                $plain = $this->flattenHtmlTextToSingleLine($row->short_description);
                if ($plain !== '') {
                    return $plain;
                }
            }
        }

        $product->loadMissing('parent', 'attribute_values.attribute');
        if ($product->parent) {
            $product->parent->loadMissing('attribute_values.attribute');
        }

        foreach ($candidateIds as $pid) {
            $p = ((int) $product->id === $pid) ? $product : $product->parent;
            if (! $p) {
                continue;
            }

            $plain = $this->shortDescriptionFromLoadedAttributes($p);
            if ($plain !== '') {
                return $plain;
            }
        }

        return '';
    }

    protected function shortDescriptionFromLoadedAttributes($product): string
    {
        if (! $product || ! $product->relationLoaded('attribute_values')) {
            return '';
        }

        foreach ($product->attribute_values as $av) {
            if ($av->attribute && $av->attribute->code === 'short_description') {
                $col = $av->attribute->column_name;
                $raw = $av->{$col} ?? null;
                $plain = $this->flattenHtmlTextToSingleLine($raw);
                if ($plain !== '') {
                    return $plain;
                }
            }
        }

        return '';
    }

    protected function flattenHtmlTextToSingleLine(mixed $raw): string
    {
        if ($raw === null) {
            return '';
        }

        $text = trim(strip_tags((string) $raw));
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }

    /**
     * Per-line DHL commodity codes from product `hs_code` attribute (variant first, then configurable parent).
     *
     * @return list<array{typeCode: string, value: string}>
     */
    protected function commodityCodesForProduct($product): array
    {
        $defaultOut = $this->dhlConfig('default_commodity_code_outbound', '999999.00.00');
        $defaultIn = $this->dhlConfig('default_commodity_code_inbound', '999999.00.00');

        $code = $this->resolveHsCodeFromProduct($product);
        if ($code !== '') {
            return [
                ['typeCode' => 'outbound', 'value' => $code],
                ['typeCode' => 'inbound', 'value' => $code],
            ];
        }

        return [
            ['typeCode' => 'outbound', 'value' => $defaultOut],
            ['typeCode' => 'inbound', 'value' => $defaultIn],
        ];
    }

    /**
     * Read `hs_code` from the shipped SKU’s attribute values, using the correct value column per attribute type.
     * Configurable: use variant’s code when set; otherwise inherit from parent.
     */
    protected function resolveHsCodeFromProduct($product): string
    {
        if (! $product) {
            return '';
        }

        $product->loadMissing('parent');
        $candidates = array_values(array_filter([$product, $product->parent]));

        foreach ($candidates as $p) {
            $p->loadMissing('attribute_values.attribute');
            foreach ($p->attribute_values as $av) {
                if (! $av->attribute || $av->attribute->code !== 'hs_code') {
                    continue;
                }

                $attr = $av->attribute;

                if ($attr->type === 'select') {
                    $optionId = (int) ($av->integer_value ?? 0);
                    if ($optionId <= 0) {
                        continue;
                    }

                    $attr->loadMissing('options');
                    $option = $attr->options->firstWhere('id', $optionId);
                    if ($option) {
                        $trimmed = trim((string) ($option->admin_name ?? ''));
                        if ($trimmed !== '') {
                            return $trimmed;
                        }
                    }

                    continue;
                }

                $col = $attr->column_name;
                $raw = $av->{$col} ?? null;
                if ($raw === null || $raw === '') {
                    continue;
                }

                $trimmed = trim(is_string($raw) ? $raw : (string) $raw);
                if ($trimmed !== '') {
                    return $trimmed;
                }
            }
        }

        return '';
    }

    protected function buildOutputImageProperties(bool $isInternational): array
    {
        $label = $this->dhlConfig('template_label') ?: 'ECOM26_84_A4_001';
        $waybill = $this->dhlConfig('template_waybill') ?: 'ARCH_8X4_A4_002';
        $invoice = $this->dhlConfig('template_invoice') ?: 'COMMERCIAL_INVOICE_P_10';
        $lang = $this->dhlConfig('invoice_language_code') ?: 'eng';

        $imageOptions = [
            [
                'typeCode'     => 'label',
                'templateName' => $label,
            ],
            [
                'typeCode'          => 'waybillDoc',
                'templateName'      => $waybill,
                'isRequested'       => true,
                'hideAccountNumber' => true,
            ],
        ];

        if ($isInternational) {
            $imageOptions[] = [
                'typeCode'     => 'invoice',
                'templateName' => $invoice,
                'invoiceType'  => 'commercial',
                'languageCode' => $lang,
                'isRequested'  => true,
            ];
        }

        return [
            'allDocumentsInOneImage' => true,
            'encodingFormat'         => 'pdf',
            'imageOptions'           => $imageOptions,
        ];
    }

    protected function extractPdfBase64(array $data): ?string
    {
        if (! empty($data['documents']) && is_array($data['documents'])) {
            foreach ($data['documents'] as $doc) {
                if (! empty($doc['content'])) {
                    return $doc['content'];
                }
            }
        }

        if (! empty($data['labelImage']) && is_array($data['labelImage'])) {
            foreach ($data['labelImage'] as $img) {
                if (! empty($img['graphicImage'])) {
                    return $img['graphicImage'];
                }
            }
        }

        return null;
    }

    protected function storeShipmentPdf(Order $order, string $base64): string
    {
        $binary = base64_decode($base64, true);
        if ($binary === false || $binary === '') {
            throw new \RuntimeException('Invalid PDF payload from DHL');
        }

        $relative = 'dhl/orders/'.$order->id.'/'.uniqid('dhl_', true).'.pdf';
        Storage::disk('local')->put($relative, $binary);

        return $relative;
    }

    /**
     * @return array{postcode: string, city: string, country: string, address1: string, address2: string}
     */
    protected function getOriginAddress()
    {
        return [
            'postcode'  => $this->dhlConfig('origin_postcode') ?? '12345',
            'city'      => $this->dhlConfig('origin_city') ?? 'City',
            'country'   => $this->dhlConfig('origin_country') ?? 'US',
            'address1'  => $this->dhlConfig('origin_address') ?? '',
            'address2'  => $this->dhlConfig('origin_address_line_2') ?? '',
        ];
    }

    /**
     * Align with DHL carrier: Nigeria FCT postcodes 900xxx → cityName Abuja for API validation.
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

        if (preg_match('/^90\d{4}$/', $digits)) {
            return 'Abuja';
        }

        return $city;
    }

    /**
     * Receiver address for DHL paperwork: street in line1, district/area in line2 when city is normalized
     * (e.g. AMAC + Abuja), optional postcode in line3 — max 45 chars per line. Omits store company_name.
     *
     * @return array{line1: string, line2: string, line3: string}
     */
    /**
     * Bagisto renamed DB column `address1` → `address`; older code reading address1 sees empty street.
     */
    protected function resolveOrderStreetLine(object $shippingAddress): string
    {
        return trim((string) ($shippingAddress->address ?? $shippingAddress->address1 ?? ''));
    }

    protected function resolveOrderAddressLine2(object $shippingAddress): string
    {
        return trim((string) ($shippingAddress->address2 ?? ''));
    }

    /**
     * DHL requires `companyName` on receiver contactInformation (422 if omitted).
     * Default to person name so store/channel `company_name` (e.g. AsoSignature) is not used on labels.
     *
     * @return array<string, string>
     */
    protected function buildReceiverContactInformation(
        object $shippingAddress,
        string $receiverEmail,
        string $receiverPersonName,
        string $receiverPhoneE164
    ): array {
        $companyName = $receiverPersonName;

        if ($this->dhlConfig('dhl_receiver_show_company_on_label')) {
            $company = trim((string) ($shippingAddress->company_name ?? ''));
            if ($company !== '') {
                $companyName = $company;
            }
        }

        return [
            'phone'         => $receiverPhoneE164,
            'email'         => $receiverEmail,
            'fullName'      => $receiverPersonName,
            'companyName'   => $companyName,
        ];
    }

    /**
     * Prefer shipping phone, then customer account, then billing address (checkout sometimes omits shipping phone).
     */
    protected function resolveReceiverPhoneRaw(Order $order, object $shippingAddress): string
    {
        $candidates = [
            trim((string) ($shippingAddress->phone ?? '')),
        ];

        if ($order->customer) {
            $candidates[] = trim((string) ($order->customer->phone ?? ''));
        }

        $billing = $order->billing_address;
        if ($billing) {
            $candidates[] = trim((string) ($billing->phone ?? ''));
        }

        foreach ($candidates as $p) {
            if ($p !== '') {
                return $p;
            }
        }

        return '';
    }

    /**
     * MyDHL examples use E.164 (e.g. +234…). Local NG numbers (070…) must be normalized or labels omit phone.
     */
    protected function formatPhoneForDhl(?string $phone, string $countryCode): string
    {
        $raw = trim((string) ($phone ?? ''));
        if ($raw === '') {
            $cc = strtoupper(trim($countryCode));

            return $cc === 'NG' ? '+234800000000' : '1234567890';
        }

        $sanitized = $this->sanitizePhone($raw);
        $cc = strtoupper(trim($countryCode));

        if ($cc === 'NG') {
            if (str_starts_with($sanitized, '+234')) {
                return $sanitized;
            }
            if (str_starts_with($sanitized, '234') && strlen($sanitized) >= 13) {
                return '+'.$sanitized;
            }
            if (str_starts_with($sanitized, '0') && strlen($sanitized) >= 11) {
                return '+234'.substr($sanitized, 1);
            }
            if (strlen($sanitized) === 10 && preg_match('/^[789]/', $sanitized)) {
                return '+234'.$sanitized;
            }
        }

        if (str_starts_with($sanitized, '+')) {
            return $sanitized;
        }

        return $sanitized;
    }

    protected function buildReceiverPostalAddressLines($shippingAddress): array
    {
        $a1 = $this->resolveOrderStreetLine($shippingAddress);
        $a2 = $this->resolveOrderAddressLine2($shippingAddress);
        $cityRaw = trim((string) ($shippingAddress->city ?? ''));
        $stateRaw = trim((string) ($shippingAddress->state ?? ''));
        $postcodeRaw = trim((string) ($shippingAddress->postcode ?? ''));
        $pcDigits = preg_replace('/\D/', '', $postcodeRaw);

        $dhlCity = $this->normalizeDhlCityNameForRates(
            $shippingAddress->country ?? '',
            $postcodeRaw,
            $cityRaw
        );

        // Line 1: street (from `address` column — checkout "Street Address")
        $line1 = $a1;
        if ($line1 === '') {
            $line1 = $a2 !== '' ? $a2 : ($cityRaw !== '' ? $cityRaw : ($postcodeRaw !== '' ? $postcodeRaw : 'Address'));
        }

        // Line 2: address2, or city (e.g. AMAC) when different from DHL city, or state
        $line2 = $a2;
        if ($line2 === '' && $cityRaw !== '' && strcasecmp($cityRaw, $dhlCity) !== 0) {
            $line2 = $cityRaw;
        }
        if ($line2 === '' && $stateRaw !== '' && strcasecmp($stateRaw, $dhlCity) !== 0) {
            $line2 = $stateRaw;
        }
        if ($line2 === '') {
            $line2 = $postcodeRaw !== '' ? $postcodeRaw : $dhlCity;
        }

        // Line 3: postcode on the label when not already embedded in line 1–2
        $line3 = '';
        if ($postcodeRaw !== '' && $pcDigits !== '') {
            $haystack = preg_replace('/\s+/', '', $line1.$line2);
            if (stripos($haystack, $pcDigits) === false) {
                $line3 = $postcodeRaw;
            }
        }

        if (strcasecmp(trim($line3), trim($line2)) === 0) {
            $line3 = '';
        }

        return [
            'line1' => $this->truncateDhlAddressLine($this->sanitizeAddressLine($line1)),
            'line2' => $this->truncateDhlAddressLine($this->sanitizeAddressLine($line2)),
            'line3' => $line3 !== '' ? $this->truncateDhlAddressLine($this->sanitizeAddressLine($line3)) : '',
        ];
    }

    protected function calculatePackageDimensions(Order $order, array $shipmentData)
    {
        $defaultLength = (float) ($this->dhlConfig('default_length') ?? 10);
        $defaultWidth = (float) ($this->dhlConfig('default_width') ?? 10);
        $defaultHeight = (float) ($this->dhlConfig('default_height') ?? 10);

        $maxLength = $defaultLength;
        $maxWidth = $defaultWidth;
        $maxHeight = $defaultHeight;

        if (! isset($shipmentData['items']) || ! is_array($shipmentData['items'])) {
            return [
                'length' => $maxLength,
                'width'  => $maxWidth,
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
            'width'  => $maxWidth,
            'height' => $maxHeight,
        ];
    }

    protected function getNextBusinessDay(): \Carbon\Carbon
    {
        $date = now();

        if ($date->hour >= 15) {
            $date = $date->addDay();
        }

        while ($date->dayOfWeek === 0 || $date->dayOfWeek === 6) {
            $date = $date->addDay();
        }

        return $date->setTime(10, 0, 0);
    }

    /**
     * Same rules as DHL carrier rates: non-empty lines; sensible fallbacks when street lines missing.
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
     * MyDHL API: postal address lines maxLength 45.
     */
    protected function truncateDhlAddressLine(string $addressLine, int $maxLength = 45): string
    {
        if (function_exists('mb_strlen') && mb_strlen($addressLine, 'UTF-8') > $maxLength) {
            return mb_substr($addressLine, 0, $maxLength, 'UTF-8');
        }

        return strlen($addressLine) > $maxLength ? substr($addressLine, 0, $maxLength) : $addressLine;
    }

    protected function sanitizeAddressLine($addressLine)
    {
        $addressLine = trim($addressLine ?? '');

        if ($addressLine === '' || ! preg_match('/\S/', $addressLine)) {
            return 'Address';
        }

        return $addressLine;
    }

    protected function sanitizePhone($phone)
    {
        $phone = trim($phone ?? '');
        $phone = preg_replace('/[^\d+]/', '', $phone);

        if ($phone === '') {
            return '1234567890';
        }

        return $phone;
    }
}
