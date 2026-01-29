<?php

namespace Webkul\Core\Helpers\Exchange;

use Illuminate\Support\Facades\Log;
use Webkul\Core\Repositories\CurrencyRepository;
use Webkul\Core\Repositories\ExchangeRateRepository;

class FlutterwaveExchange extends ExchangeRate
{
    protected $apiKey;
    protected $apiBaseUrl;

    public function __construct(
        protected CurrencyRepository $currencyRepository,
        protected ExchangeRateRepository $exchangeRateRepository
    ) {
        $config = config('services.exchange_api.flutterwave');

        $this->apiKey = $config['key'] ?? null;

        // Use v3 API which works with existing Flutterwave keys
        $this->apiBaseUrl = 'https://api.flutterwave.com/v3';
    }

    /**
     * Update exchange rates using Flutterwave API
     *
     * @return void
     * @throws \Exception
     */
    public function updateRates()
    {
        Log::info('Flutterwave Exchange: Starting exchange rate update', [
            'api_base_url' => $this->apiBaseUrl,
        ]);

        if (empty($this->apiKey)) {
            Log::error('Flutterwave Exchange: API key not configured');
            throw new \Exception('Flutterwave API key is not configured. Please set FLUTTERWAVE_SECRET_KEY in your .env file.');
        }

        $currencies = $this->currencyRepository->all();
        $baseCurrency = config('app.currency');

        Log::info('Flutterwave Exchange: Fetching rates', [
            'base_currency'    => $baseCurrency,
            'total_currencies' => $currencies->count(),
        ]);

        $client = new \GuzzleHttp\Client([
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ]);

        $successCount = 0;
        $failedCount = 0;

        foreach ($currencies as $currency) {
            // Skip base currency
            if ($currency->code === $baseCurrency) {
                Log::debug('Flutterwave Exchange: Skipping base currency', ['currency' => $baseCurrency]);
                continue;
            }

            try {
                $rate = $this->fetchRate($client, $baseCurrency, $currency->code);

                if ($rate !== null) {
                    $this->exchangeRateRepository->updateOrCreate(
                        ['target_currency' => $currency->id],
                        ['rate' => $rate]
                    );

                    Log::info('Flutterwave Exchange: Rate updated successfully', [
                        'base_currency'   => $baseCurrency,
                        'target_currency' => $currency->code,
                        'rate'            => $rate,
                    ]);

                    $successCount++;
                } else {
                    Log::warning('Flutterwave Exchange: Rate returned null', [
                        'target_currency' => $currency->code,
                    ]);
                    $failedCount++;
                }
            } catch (\Exception $e) {
                Log::warning('Flutterwave Exchange: Failed to fetch rate', [
                    'target_currency' => $currency->code,
                    'error'           => $e->getMessage(),
                ]);
                $failedCount++;
                continue;
            }
        }

        Log::info('Flutterwave Exchange: Update completed', [
            'successful' => $successCount,
            'failed'     => $failedCount,
        ]);
    }

    /**
     * Fetch exchange rate from Flutterwave v3 API
     *
     * Uses the GET /v3/rates endpoint with query parameters:
     * - from: source currency (base currency)
     * - to: destination currency (target currency)
     * - amount: amount to convert (we use 1 to get the rate)
     *
     * @param \GuzzleHttp\Client $client
     * @param string $baseCurrency
     * @param string $targetCurrency
     * @return float|null
     */
    protected function fetchRate($client, $baseCurrency, $targetCurrency)
    {
        $endpoint = $this->apiBaseUrl . '/rates';

        $queryParams = [
            'from'   => $baseCurrency,
            'to'     => $targetCurrency,
            'amount' => 1,
        ];

        Log::debug('Flutterwave Exchange: API Request', [
            'endpoint' => $endpoint,
            'params'   => $queryParams,
        ]);

        $startTime = microtime(true);

        $response = $client->request('GET', $endpoint, [
            'query' => $queryParams,
        ]);

        $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        $statusCode = $response->getStatusCode();
        $responseBody = $response->getBody()->getContents();
        $result = json_decode($responseBody, true);

        Log::debug('Flutterwave Exchange: API Response', [
            'status_code'   => $statusCode,
            'response_time' => $responseTime . 'ms',
            'response'      => $result,
        ]);

        if (!isset($result['status']) || $result['status'] !== 'success') {
            $errorMessage = $result['message'] ?? 'Unknown error from Flutterwave API';
            Log::error('Flutterwave Exchange: API returned error', [
                'error'    => $errorMessage,
                'response' => $result,
            ]);
            throw new \Exception($errorMessage);
        }

        // v3 API returns the rate in data.rate or data.to.amount
        // Response format: { "status": "success", "data": { "rate": 1500.50, "source": {...}, "destination": {...} } }
        if (isset($result['data']['rate'])) {
            $rate = (float) $result['data']['rate'];

            Log::debug('Flutterwave Exchange: Rate extracted', [
                'base_currency'   => $baseCurrency,
                'target_currency' => $targetCurrency,
                'rate'            => $rate,
            ]);

            return $rate;
        }

        // Alternative response format: data.to.amount when converting 1 unit
        if (isset($result['data']['to']['amount'])) {
            $rate = (float) $result['data']['to']['amount'];

            Log::debug('Flutterwave Exchange: Rate extracted from to.amount', [
                'base_currency'   => $baseCurrency,
                'target_currency' => $targetCurrency,
                'rate'            => $rate,
            ]);

            return $rate;
        }

        Log::warning('Flutterwave Exchange: Could not extract rate from response', [
            'response' => $result,
        ]);

        return null;
    }

    /**
     * Get a single exchange rate between two currencies
     *
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param float $amount
     * @return array|null
     */
    public function getRate($fromCurrency, $toCurrency, $amount = 1)
    {
        Log::info('Flutterwave Exchange: getRate called', [
            'from_currency' => $fromCurrency,
            'to_currency'   => $toCurrency,
            'amount'        => $amount,
        ]);

        if (empty($this->apiKey)) {
            Log::error('Flutterwave Exchange: API key not configured for getRate');
            throw new \Exception('Flutterwave API key is not configured.');
        }

        $client = new \GuzzleHttp\Client([
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ]);

        $endpoint = $this->apiBaseUrl . '/rates';

        $queryParams = [
            'from'   => $fromCurrency,
            'to'     => $toCurrency,
            'amount' => $amount,
        ];

        Log::debug('Flutterwave Exchange: getRate API Request', [
            'endpoint' => $endpoint,
            'params'   => $queryParams,
        ]);

        try {
            $startTime = microtime(true);

            $response = $client->request('GET', $endpoint, [
                'query' => $queryParams,
            ]);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $result = json_decode($responseBody, true);

            Log::debug('Flutterwave Exchange: getRate API Response', [
                'status_code'   => $statusCode,
                'response_time' => $responseTime . 'ms',
                'response'      => $result,
            ]);

            if (isset($result['status']) && $result['status'] === 'success') {
                $rateData = [
                    'rate'          => (float) ($result['data']['rate'] ?? $result['data']['to']['amount'] ?? 0),
                    'from_amount'   => (float) ($result['data']['from']['amount'] ?? $amount),
                    'from_currency' => $result['data']['from']['currency'] ?? $fromCurrency,
                    'to_amount'     => (float) ($result['data']['to']['amount'] ?? 0),
                    'to_currency'   => $result['data']['to']['currency'] ?? $toCurrency,
                ];

                Log::info('Flutterwave Exchange: getRate successful', $rateData);

                return $rateData;
            }

            Log::warning('Flutterwave Exchange: getRate returned non-success status', [
                'response' => $result,
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;
            Log::error('Flutterwave Exchange: getRate client error', [
                'error'         => $e->getMessage(),
                'status_code'   => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
                'response_body' => $responseBody,
            ]);
            throw $e;
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;
            Log::error('Flutterwave Exchange: getRate server error', [
                'error'         => $e->getMessage(),
                'status_code'   => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null,
                'response_body' => $responseBody,
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Flutterwave Exchange: getRate unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        return null;
    }
}
