<?php

namespace Kantorge\CurrencyExchangeRates\Tests;

use Carbon\Carbon;
use Kantorge\CurrencyExchangeRates\CurrencyExchangeRateApiClientFactory;

class MockApiClientTest extends TestCase
{
    public function test_get_supported_currencies()
    {
        $client = CurrencyExchangeRateApiClientFactory::create('mock');

        $currencies = $client->getSupportedCurrencies();

        $this->assertIsArray($currencies);
        $this->assertCount(3, $currencies);
        $this->assertContains('USD', $currencies);
        $this->assertContains('EUR', $currencies);
        $this->assertContains('HUF', $currencies);
    }

    public function test_is_currency_supported()
    {
        $client = CurrencyExchangeRateApiClientFactory::create('mock');

        $this->assertTrue($client->isCurrencySupported('USD'));
        $this->assertTrue($client->isCurrencySupported('EUR'));
        $this->assertTrue($client->isCurrencySupported('HUF'));
        $this->assertFalse($client->isCurrencySupported('GBP'));
    }

    public function test_time_series_returns_expected_values()
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
