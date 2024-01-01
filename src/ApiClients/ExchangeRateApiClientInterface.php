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
     * @param  string  $currency Currency ISO code to check against the API
     */
    public function isCurrencySupported(string $currency): bool;
}
