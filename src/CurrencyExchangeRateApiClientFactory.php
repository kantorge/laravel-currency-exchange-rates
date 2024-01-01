<?php

namespace Kantorge\CurrencyExchangeRates;

use Kantorge\CurrencyExchangeRates\ApiClients\ExchangeRateApiClientInterface;
use Kantorge\CurrencyExchangeRates\ApiClients\FrankfurterApiClient;
use Kantorge\CurrencyExchangeRates\ApiClients\MockApiClient;

class CurrencyExchangeRateApiClientFactory
{
    public static function create(string $type): ExchangeRateApiClientInterface
    {
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