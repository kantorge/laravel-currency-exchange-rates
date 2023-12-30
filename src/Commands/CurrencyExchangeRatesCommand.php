<?php

namespace Kantorge\CurrencyExchangeRates\Commands;

use Illuminate\Console\Command;

class CurrencyExchangeRatesCommand extends Command
{
    public $signature = 'laravel-currency-exchange-rates';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
