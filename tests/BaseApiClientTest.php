<?php

namespace Kantorge\CurrencyExchangeRates\Tests;

use Carbon\Carbon;
use Kantorge\CurrencyExchangeRates\CurrencyExchangeRateApiClientFactory;

class BaseApiClientTest extends TestCase
{
    public function testTimeseriesThrowsExceptionForInvalidArguments()
    {
        $client = CurrencyExchangeRateApiClientFactory::create('mock');

        // Test invalid start date
        $this->expectException(\InvalidArgumentException::class);
        $client->getTimeSeries(
            new Carbon('tomorrow'),
            new Carbon('tomorrow'),
            'USD',
            ['EUR', 'HUF']
        );

        $this->expectException(\InvalidArgumentException::class);
        $client->getTimeSeries(
            new Carbon('today'),
            new Carbon('yesterday'),
            'USD',
            ['EUR', 'HUF']
        );

        // Test invalid end date
        $this->expectException(\InvalidArgumentException::class);
        $client->getTimeSeries(
            new Carbon('today'),
            new Carbon('tomorrow'),
            'USD',
            ['EUR', 'HUF']
        );

        // Test invalid base currency
        $this->expectException(\InvalidArgumentException::class);
        $client->getTimeSeries(
            new Carbon('today'),
            new Carbon('today'),
            'GBP',
            ['EUR', 'HUF']
        );

        $this->expectException(\InvalidArgumentException::class);
        $client->getTimeSeries(
            new Carbon('today'),
            new Carbon('today'),
            'EUR',
            ['EUR', 'HUF']
        );

        // Test invalid currencies
        $this->expectException(\InvalidArgumentException::class);
        $client->getTimeSeries(
            new Carbon('today'),
            new Carbon('today'),
            'USD',
            ['GBP', 'HUF']
        );

        // Test valid arguments
        $this->expecttNotToPerformAssertions();
        $client->getTimeSeries(
            new Carbon('today'),
            new Carbon('today'),
            'USD',
            ['EUR', 'HUF']
        );
    }

    public function testDefaultProviderIsLoaded()
    {
        $client = CurrencyExchangeRateApiClientFactory::create();

        $this->assertInstanceOf(
            \Kantorge\CurrencyExchangeRates\ApiClients\FrankfurterApiClient::class,
            $client
        );
    }
}
