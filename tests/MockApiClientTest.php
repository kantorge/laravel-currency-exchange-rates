<?php

namespace Kantorge\CurrencyExchangeRates\Tests;

use Kantorge\CurrencyExchangeRates\CurrencyExchangeRateApiClientFactory;
use Carbon\Carbon;

class MockApiClientTest extends TestCase
{
    public function testGetSupportedCurrencies()
    {
        $client = CurrencyExchangeRateApiClientFactory::create('mock');

        $currencies = $client->getSupportedCurrencies();

        $this->assertIsArray($currencies);
        $this->assertCount(3, $currencies);
        $this->assertContains('USD', $currencies);
        $this->assertContains('EUR', $currencies);
        $this->assertContains('HUF', $currencies);
    }

    public function testIsCurrencySupported()
    {
        $client = CurrencyExchangeRateApiClientFactory::create('mock');

        $this->assertTrue($client->isCurrencySupported('USD'));
        $this->assertTrue($client->isCurrencySupported('EUR'));
        $this->assertTrue($client->isCurrencySupported('HUF'));
        $this->assertFalse($client->isCurrencySupported('GBP'));
    }

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

    public function testTimeSeriesReturnsExpectedValues()
    {
        $client = CurrencyExchangeRateApiClientFactory::create('mock');

        $data = $client->getTimeSeries(
            new Carbon('2023-01-01'),
            new Carbon('2023-01-01'),
            'USD',
            ['EUR', 'HUF']
        );

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('2023-01-01', $data);
        $this->assertIsArray($data['2023-01-01']);
        $this->assertCount(2, $data['2023-01-01']);
        $this->assertArrayHasKey('EUR', $data['2023-01-01']);
        $this->assertArrayHasKey('HUF', $data['2023-01-01']);
        $this->assertEquals(0.9, $data['2023-01-01']['EUR']);
        $this->assertEquals(0.8, $data['2023-01-01']['HUF']);

        $data = $client->getTimeSeries(
            new Carbon('2023-01-01'),
            new Carbon('2023-01-03'),
            'USD',
            ['EUR', 'HUF']
        );

        $this->assertIsArray($data);
        $this->assertCount(3, $data);
    }
}