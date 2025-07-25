<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Webkul\Core\Models\CurrencyExchangeRate;
use Webkul\Core\Models\Currency;

class UpdateCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-currency-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = config('services.currency.url');
        $apiKey = config('services.currency.key');
        $base = config('services.currency.base', 'USD');
        $symbols = config('services.currency.symbols');

        $response = Http::withHeaders([
            'access_key' => $apiKey
        ])->get($url, [
            'access_key' => $apiKey,
            'source' => $base,
            'currencies' => $symbols,
        ]);

        $data = $response->json();

        if (!isset($data['rates'])) {
            $this->error('Failed to fetch exchange rates.');
            return;
        }

        foreach ($data['quotes'] as $currency => $rate) {
            $currency = str_replace('USD', '', $currency); // Adjust if base currency is not USD

            // get all the curencies from the database
            $currency = strtoupper($currency); // Ensure currency code is uppercase
            if (!in_array($currency, explode(',', $symbols))) {
                continue; // Skip currencies not in the configured symbols
            }

            $db_currency = Currency::where('code', $currency)->first();
            if (!$db_currency) {
                $this->error("Currency {$currency} not found in the database.");
                continue;
            }
            // Update or create the exchange rate for the currency

            CurrencyExchangeRate::updateOrCreate(
                ['target_currency' => $db_currency->id],
                ['rate' => $rate, 'updated_at' => now()]
            );
            $this->info("Updated rate for {$currency}: {$rate} (USD base) at " . now()->toDateTimeString());
        }

        $this->info('Currency exchange rates updated successfully.');

    }
}
