<?php

namespace Kantorge\CurrencyExchangeRates\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kantorge\CurrencyExchangeRates\CurrencyExchangeRatesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
        protected function getPackageProviders($app)
    {
        return [
            CurrencyExchangeRatesServiceProvider::class,
        ];
    }
}
