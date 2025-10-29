<?php

namespace Kantorge\CurrencyExchangeRates\ApiClients;

use Carbon\Carbon;

interface ExchangeRateApiClientInterface
{
    public function getTimeSeries(Carbon $startDate, Carbon $endDate, string $baseCurrency, array $currencies);

    /**
     * Get an array of currencies, that is supported by the given API. It returns an array of currency ISO codes.
     * For example: ['USD', 'EUR', 'GBP']
     */
    public function getSupportedCurrencies(): array;

    /**
     * Check if the given currency is supported by the API.
     *
     * @param  string  $currency  Currency ISO code to check against the API
     */
    public function isCurrencySupported(string $currency): bool;

    /**
     * Get the base URL for the exchange rate API.
     *
     * This method returns the base URL that will be used for making requests
     * to the exchange rate API service.
     *
     * @return string The base URL of the API endpoint
     */
    public function getBaseUrl(): string;

    /**
     * Clear cached data for a specific cache key.
     *
     * This method removes cached exchange rate data associated with the given key,
     * forcing fresh data to be fetched on the next request for that key.
     *
     * @param  string  $key  The cache key to clear
     */
    public function clearCacheForKey(string $key): void;
}
