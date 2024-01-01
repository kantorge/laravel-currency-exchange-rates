<?php

namespace Kantorge\CurrencyExchangeRates\ApiClients;

use Illuminate\Support\Facades\Cache;

class FrankfurterApiClient extends BaseCurrencyClient implements ExchangeRateApiClientInterface
{
    protected string $baseUrl = 'https://api.frankfurter.app';

    public const IDENTIFIER = 'frankfurter';

    public function getTimeSeries($startDate, $endDate, $baseCurrency, array $currencies)
    {
        $this->verifyTimeSeriesArguments($startDate, $endDate, $baseCurrency, $currencies);

        // Create a cache key
        $cacheKey = $this->getCacheKey(sprintf(
            'time_series_%s_%s_%s_%s',
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            $baseCurrency,
            implode('_', $currencies)
        ));

        // Get data from the API
        $data = Cache::remember($cacheKey, config('currency-exchange-rates.frankfurter.cache_ttl'), function () use ($startDate, $endDate, $baseCurrency, $currencies) {
            $params = [
                'from' => $baseCurrency,
                'to' => implode(',', $currencies),
            ];

            return $this->makeApiRequest(
                sprintf('/timeseries/%s..%s', $startDate->format('Y-m-d'), $endDate->format('Y-m-d')),
                $params
            )['rates'];
        });
    }

    public function getSupportedCurrencies(): array
    {
        // Create a cache key
        $cacheKey = $this->getCacheKey('supported_currencies');

        return Cache::remember($cacheKey, config('currency-exchange-rates.frankfurter.cache_ttl'), function () {
            $data = $this->makeApiRequest('/currencies');

            return array_keys($data);
        });
    }
}
