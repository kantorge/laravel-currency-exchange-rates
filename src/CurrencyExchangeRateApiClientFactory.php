<?php

namespace Kantorge\CurrencyExchangeRates;

use Kantorge\CurrencyExchangeRates\ApiClients\ExchangeRateApiClientInterface;
use Kantorge\CurrencyExchangeRates\ApiClients\FrankfurterApiClient;
use Kantorge\CurrencyExchangeRates\ApiClients\MockApiClient;

class CurrencyExchangeRateApiClientFactory
{
    /**
     * Creates an instance of the Currency Exchange Rates based on the specified type.
     *
     * @param  string|null  $type  The type of the client to create. If null, the default provider from the configuration will be used.
     * @return ExchangeRateApiClientInterface The created instance of the Currency Exchange Rates.
     *
     * @throws \Exception If an invalid client type is specified.
     */
    public static function create(?string $type = null): ExchangeRateApiClientInterface
    {
        if ($type === null) {
            $type = config('currency-exchange-rates.default_provider');
        }

        switch ($type) {
            case FrankfurterApiClient::IDENTIFIER:
                return new FrankfurterApiClient();
            case MockApiClient::IDENTIFIER:
                return new MockApiClient();
            default:
                throw new \Exception("Invalid client type: $type");
        }
    }
}
