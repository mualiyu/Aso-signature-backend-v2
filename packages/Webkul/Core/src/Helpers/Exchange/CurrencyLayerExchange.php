<?php

namespace Webkul\Core\Helpers\Exchange;

use Webkul\Core\Repositories\CurrencyRepository;
use Webkul\Core\Repositories\ExchangeRateRepository;

class CurrencyLayerExchange extends ExchangeRate
{
    protected $apiKey;
    protected $apiEndPoint;

    public function __construct(
        protected CurrencyRepository $currencyRepository,
        protected ExchangeRateRepository $exchangeRateRepository
    ) {
        $this->apiEndPoint = 'https://apilayer.net/api/live';
        $this->apiKey = config('services.exchange_api')['currencylayer']['key'];
    }

    public function updateRates()
    {
        $currencies = $this->currencyRepository->all();

        // Filter out base currency and collect currency codes
        $currencyCodes = $currencies->where('code', '!=', config('app.currency'))
            ->pluck('code')
            ->implode(',');

        if (empty($currencyCodes)) {
            return;
        }

        $client = new \GuzzleHttp\Client;

        // Make single API call for all currencies
        $result = $client->request('GET', $this->apiEndPoint, [
            'query' => [
                'access_key' => $this->apiKey,
                'source' => config('app.currency'),
                'currencies' => $currencyCodes,
                'format' => 1 // Optional: format the response as JSON
            ]
        ]);

        $result = json_decode($result->getBody()->getContents(), true);

        if (isset($result['success']) && !$result['success']) {
            throw new \Exception($result['error']['info'] ?? $result['error']['type'], 1);
        }

        // Update exchange rates for all currencies
        foreach ($currencies as $currency) {
            if ($currency->code == config('app.currency')) {
                continue;
            }

            if (!isset($result['quotes'])) {
                continue;
            }

            // do something like this   $currency = str_replace('USD', '', $currency); for the quotes
            $currencyCode = 'USD' . $currency->code; // Assuming the API returns quotes in the format USDXXX
            if (!isset($result['quotes'][$currencyCode])) {
                continue; // Skip if the currency quote is not available
            }
            $this->exchangeRateRepository->updateOrCreate(
                ['target_currency' => $currency->id],
                ['rate' => $result['quotes'][$currencyCode]]
            );
        }
    }
}
