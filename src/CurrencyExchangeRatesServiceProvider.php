<?php

namespace Kantorge\CurrencyExchangeRates;

use Illuminate\Support\ServiceProvider;
use Kantorge\CurrencyExchangeRates\CurrencyExchangeRateApiClientFactory As CurrencyExchangeRates;

class CurrencyExchangeRatesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/currency-exchange-rates.php', 'currency-exchange-rates');

        $this->app->bind('currency-exchange-rates', function () {
            return new CurrencyExchangeRates();
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/currency-exchange-rates.php' => config_path('currency-exchange-rates.php'),
        ], 'laravel-currency-exchange-rates-config');
    }
}
