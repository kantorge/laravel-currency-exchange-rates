<?php

namespace Kantorge\CurrencyExchangeRates\ApiClients;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyBeaconApiClient extends BaseCurrencyClient implements ExchangeRateApiClientInterface
{
    protected string $baseUrl = 'https://api.currencybeacon.com/v1';

    public const IDENTIFIER = 'currencybeacon';

    /**
     * Override the makeApiRequest method to include the API key and handle authentication errors.
     */
    protected function makeApiRequest(string $endpoint, array $params = [])
    {
        $apiKey = config('currency-exchange-rates.currencybeacon.api_key');

        if (empty($apiKey)) {
            throw new \RuntimeException(
                'CurrencyBeacon API key is not configured. Please set CURRENCY_BEACON_API_KEY in your .env file.'
            );
        }

        // Add API key to parameters
        $params['api_key'] = $apiKey;

        try {
            $response = Http::get($this->baseUrl . $endpoint, $params)->throw();

            // Check if the response contains an error
            $data = $response->json();
            if (isset($data['error']) && $data['error'] === true) {
                throw new \RuntimeException(
                    sprintf('CurrencyBeacon API error: %s', $data['message'] ?? 'Unknown error')
                );
            }

            return $data;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Check if it's an authentication error
            if ($e->response->status() === 401) {
                throw new \RuntimeException(
                    'CurrencyBeacon API key is invalid. Please check your CURRENCY_BEACON_API_KEY in your .env file.'
                );
            }

            throw $e;
        }
    }

    public function getTimeSeries($startDate, $endDate, $baseCurrency, array $currencies)
    {
        $this->verifyTimeSeriesArguments($startDate, $endDate, $baseCurrency, $currencies);

        // Create a cache key
        $sortedCurrencies = $currencies;
        sort($sortedCurrencies);
        $cacheKey = $this->getCacheKey(sprintf(
            'time_series_%s_%s_%s_%s',
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            $baseCurrency,
            implode('_', $sortedCurrencies)
        ));

        // Get data from the API
        $data = Cache::remember($cacheKey, config('currency-exchange-rates.currencybeacon.cache_ttl'), function () use ($startDate, $endDate, $baseCurrency, $currencies) {
            $params = [
                'base' => $baseCurrency,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'symbols' => implode(',', $currencies),
            ];

            $response = $this->makeApiRequest('/timeseries', $params);

            // Transform the response to match the expected format
            // CurrencyBeacon returns: { "response": [ { "date": "2021-01-01", "rates": { "EUR": 0.9 } } ] }
            // We need: { "2021-01-01": { "EUR": 0.9 } }
            return array_column($response['response'] ?? [], 'rates', 'date');
        });

        return $data;
    }

    public function getSupportedCurrencies(): array
    {
        // Create a cache key
        $cacheKey = $this->getCacheKey('supported_currencies');

        return Cache::remember($cacheKey, config('currency-exchange-rates.currencybeacon.cache_ttl'), function () {
            $params = [
                'type' => 'fiat',
            ];

            $data = $this->makeApiRequest('/currencies', $params);

            // Extract currency codes from the response
            // CurrencyBeacon returns: { "response": [ { "short_code": "USD", "name": "US Dollar" }, ... ] }
            return array_column($data['response'] ?? [], 'short_code');
        });
    }
}
