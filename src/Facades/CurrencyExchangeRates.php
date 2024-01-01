<?php

namespace Kantorge\CurrencyExchangeRates\Facades;

use Illuminate\Support\Facades\Facade;

class CurrencyExchangeRates extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'currency-exchange-rates';
    }
}