<?php

namespace Kantorge\CurrencyExchangeRates\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Kantorge\CurrencyExchangeRates\CurrencyExchangeRates
 */
class CurrencyExchangeRates extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Kantorge\CurrencyExchangeRates\CurrencyExchangeRates::class;
    }
}
