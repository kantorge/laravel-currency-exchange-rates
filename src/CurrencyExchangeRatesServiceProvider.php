<?php

namespace Kantorge\CurrencyExchangeRates;

use Kantorge\CurrencyExchangeRates\ApiClients\ExchangeRateApiClientInterface;
use \Illuminate\Support\ServiceProvider;

class CurrencyExchangeRatesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/currency-exchange-rates.php', 'currency-exchange-rates');
    }
}
