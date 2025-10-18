<?php

namespace Kantorge\CurrencyExchangeRates\ApiClients;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ExchangeRateApiOpenClient extends BaseCurrencyClient implements ExchangeRateApiClientInterface
{
    protected string $baseUrl = 'https://open.er-api.com/v6';

    public const IDENTIFIER = 'exchangerate-api-open';

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
        $data = Cache::remember($cacheKey, config('currency-exchange-rates.exchangerate-api-open.cache_ttl'), function () use ($startDate, $endDate, $baseCurrency, $currencies) {
            $result = [];
            
            // ExchangeRate-API Open doesn't have a native time series endpoint
            // We need to make individual requests for each date in the range
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $endDate) {
                $dateString = $currentDate->format('Y-m-d');
                
                // Make request for historical data for specific date
                $response = $this->makeApiRequest(
                    sprintf('/history/%s/%s/%s/%s', $baseCurrency, $currentDate->format('Y'), $currentDate->format('m'), $currentDate->format('d'))
                );
                
                // Extract only the requested currencies from the response
                if (isset($response['rates'])) {
                    $filteredRates = [];
                    foreach ($currencies as $currency) {
                        if (isset($response['rates'][$currency])) {
                            $filteredRates[$currency] = $response['rates'][$currency];
                        }
                    }
                    $result[$dateString] = $filteredRates;
                }
                
                $currentDate->addDay();
            }
            
            return $result;
        });

        return $data;
    }

    public function getSupportedCurrencies(): array
    {
        // Create a cache key
        $cacheKey = $this->getCacheKey('supported_currencies');

        return Cache::remember($cacheKey, config('currency-exchange-rates.exchangerate-api-open.cache_ttl'), function () {
            $data = $this->makeApiRequest('/codes');

            // The API returns supported codes in this format:
            // {
            //   "result": "success",
            //   "supported_codes": [
            //     ["USD", "United States Dollar"],
            //     ["EUR", "Euro"],
            //     ...
            //   ]
            // }
            // We need to extract just the currency codes
            if (isset($data['supported_codes']) && is_array($data['supported_codes'])) {
                return array_map(function ($codeArray) {
                    return $codeArray[0];
                }, $data['supported_codes']);
            }

            return [];
        });
    }
}
