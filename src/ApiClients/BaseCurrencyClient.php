<?php

namespace Kantorge\CurrencyExchangeRates\ApiClients;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

abstract class BaseCurrencyClient implements ExchangeRateApiClientInterface
{
    protected string $baseUrl = 'http://localhost';

    public const IDENTIFIER = 'base';

    protected function makeApiRequest(string $endpoint, array $params = [])
    {
        return Http::get($this->baseUrl.$endpoint, $params)->throw()->json();
    }

    /**
     * Get a full cache key for the given key, accounting for the cache prefix and the API identifier.
     */
    protected function getCacheKey(string $key): string
    {
        return sprintf(
            '%s_%s_%s',
            config('currency-exchange-rates.cache_prefix'),
            static::IDENTIFIER,
            $key
        );
    }

    public function clearCacheForKey(string $key): void
    {
        Cache::forget($this->getCacheKey($key));
    }

    /**
     * Common implementation for checking if a currency is supported by the API.
     */
    public function isCurrencySupported(string $currency): bool
    {
        return in_array($currency, $this->getSupportedCurrencies());
    }

    /**
     * Internal function to verify if the base currency is supported by the API.
     */
    protected function verifyBaseCurrency(string $baseCurrency): void
    {
        if (! $this->isCurrencySupported($baseCurrency)) {
            throw new \InvalidArgumentException(sprintf(
                'The base currency "%s" is not supported by the API "%s"',
                $baseCurrency,
                static::IDENTIFIER
            ));
        }
    }

    /**
     * Internal function to verify if the given currencies are supported by the API.
     */
    protected function verifyCurrencies(array $currencies): void
    {
        foreach ($currencies as $currency) {
            if (! $this->isCurrencySupported($currency)) {
                throw new \InvalidArgumentException(sprintf(
                    'The currency "%s" is not supported by the API "%s"',
                    $currency,
                    static::IDENTIFIER
                ));
            }
        }
    }

    /**
     * Internal function to verify the start and end dates for several aspects
     */
    protected function verifyDates(Carbon $startDate, Carbon $endDate): void
    {
        if ($startDate > $endDate) {
            throw new \InvalidArgumentException('The start date cannot be greater than the end date');
        }

        if ($startDate->isFuture()) {
            throw new \InvalidArgumentException('The start date cannot be in the future');
        }

        if ($endDate->isFuture()) {
            throw new \InvalidArgumentException('The end date cannot be in the future');
        }
    }

    /**
     * Internal function to verify all time series parameters
     */
    protected function verifyTimeSeriesArguments($startDate, $endDate, $baseCurrency, array $currencies): void
    {
        $this->verifyBaseCurrency($baseCurrency);
        $this->verifyCurrencies($currencies);
        $this->verifyDates($startDate, $endDate);

        // Verify that the base currency is not in the list of currencies
        if (in_array($baseCurrency, $currencies)) {
            throw new \InvalidArgumentException('The base currency cannot be in the list of currencies');
        }
    }
}
