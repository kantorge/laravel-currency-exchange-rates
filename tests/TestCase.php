<?php

namespace Kantorge\CurrencyExchangeRates\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kantorge\CurrencyExchangeRates\CurrencyExchangeRatesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Kantorge\\CurrencyExchangeRates\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            CurrencyExchangeRatesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-currency-exchange-rates_table.php.stub';
        $migration->up();
        */
    }
}
