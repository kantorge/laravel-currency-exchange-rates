<?php

namespace Kantorge\CurrencyExchangeRates;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Kantorge\CurrencyExchangeRates\Commands\CurrencyExchangeRatesCommand;

class CurrencyExchangeRatesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-currency-exchange-rates')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-currency-exchange-rates_table')
            ->hasCommand(CurrencyExchangeRatesCommand::class);
    }
}
