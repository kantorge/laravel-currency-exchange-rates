<?php

namespace Kantorge\CurrencyExchangeRates\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Kantorge\CurrencyExchangeRates\CurrencyExchangeRateApiClientFactory;

class CurrencyBeaconApiClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set a mock API key for testing
        Config::set('currency-exchange-rates.currencybeacon.api_key', 'test_api_key_12345');
    }

    public function testGetSupportedCurrencies()
    {
        $expectedResponse = [
            'response' => [
                ['short_code' => 'USD', 'name' => 'United States Dollar'],
                ['short_code' => 'EUR', 'name' => 'Euro'],
                ['short_code' => 'HUF', 'name' => 'Hungarian Forint'],
            ],
        ];

        Http::fake([
            'api.currencybeacon.com/v1/currencies*' => Http::response(
                $expectedResponse,
                200,
                []
            ),
        ]);

        $client = CurrencyExchangeRateApiClientFactory::create('currencybeacon');

        // Clear the cache before the test runs
        $client->clearCacheForKey('supported_currencies');

        $currencies = $client->getSupportedCurrencies();

        $this->assertIsArray($currencies);
        $this->assertEquals(['USD', 'EUR', 'HUF'], $currencies);
        Http::assertSentCount(1);

        // Test if a second call to the API is reading from the cache
        $currencies = $client->getSupportedCurrencies();
        Http::assertSentCount(1);
    }

    public function testGetTimeSeries()
    {
        $expectedResponse = [
            'response' => [
                [
                    'date' => '2023-01-01',
                    'rates' => [
                        'EUR' => 0.9,
                        'HUF' => 350.5,
                    ],
                ],
                [
                    'date' => '2023-01-02',
                    'rates' => [
                        'EUR' => 0.91,
                        'HUF' => 351.2,
                    ],
                ],
            ],
        ];

        Http::fake([
            'api.currencybeacon.com/v1/timeseries*' => Http::response(
                $expectedResponse,
                200,
                []
            ),
        ]);

        $client = CurrencyExchangeRateApiClientFactory::create('currencybeacon');

        // Clear the cache before the test runs
        $client->clearCacheForKey('time_series_2023-01-01_2023-01-02_USD_EUR_HUF');

        $timeSeries = $client->getTimeSeries(
            new Carbon('2023-01-01'),
            new Carbon('2023-01-02'),
            'USD',
            ['EUR', 'HUF']
        );

        $this->assertIsArray($timeSeries);
        $this->assertArrayHasKey('2023-01-01', $timeSeries);
        $this->assertArrayHasKey('2023-01-02', $timeSeries);
        $this->assertEquals(0.9, $timeSeries['2023-01-01']['EUR']);
        $this->assertEquals(350.5, $timeSeries['2023-01-01']['HUF']);
        Http::assertSentCount(1);

        // Test if a second call to the API is reading from the cache
        $timeSeries = $client->getTimeSeries(
            new Carbon('2023-01-01'),
            new Carbon('2023-01-02'),
            'USD',
            ['EUR', 'HUF']
        );
        Http::assertSentCount(1);
    }

    public function testMissingApiKeyThrowsException()
    {
        Config::set('currency-exchange-rates.currencybeacon.api_key', null);

        $client = CurrencyExchangeRateApiClientFactory::create('currencybeacon');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('CurrencyBeacon API key is not configured. Please set CURRENCY_BEACON_API_KEY in your .env file.');

        $client->getSupportedCurrencies();
    }

    public function testInvalidApiKeyThrowsException()
    {
        Config::set('currency-exchange-rates.currencybeacon.api_key', 'invalid_key');

        Http::fake([
            'api.currencybeacon.com/v1/currencies*' => Http::response(
                ['error' => true, 'message' => 'Invalid API key'],
                401,
                []
            ),
        ]);

        $client = CurrencyExchangeRateApiClientFactory::create('currencybeacon');
        $client->clearCacheForKey('supported_currencies');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('CurrencyBeacon API key is invalid. Please check your CURRENCY_BEACON_API_KEY in your .env file.');

        $client->getSupportedCurrencies();
    }

    public function testApiErrorResponseThrowsException()
    {
        Http::fake([
            'api.currencybeacon.com/v1/currencies*' => Http::response(
                ['error' => true, 'message' => 'Rate limit exceeded'],
                200,
                []
            ),
        ]);

        $client = CurrencyExchangeRateApiClientFactory::create('currencybeacon');
        $client->clearCacheForKey('supported_currencies');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('CurrencyBeacon API error: Rate limit exceeded');

        $client->getSupportedCurrencies();
    }

    public function testVerifyBaseCurrency()
    {
        $expectedResponse = [
            'response' => [
                ['short_code' => 'USD', 'name' => 'United States Dollar'],
                ['short_code' => 'EUR', 'name' => 'Euro'],
                ['short_code' => 'HUF', 'name' => 'Hungarian Forint'],
            ],
        ];

        Http::fake([
            'api.currencybeacon.com/v1/currencies*' => Http::response($expectedResponse, 200),
            'api.currencybeacon.com/v1/timeseries*' => Http::response(['response' => []], 200),
        ]);

        $client = CurrencyExchangeRateApiClientFactory::create('currencybeacon');
        $client->clearCacheForKey('supported_currencies');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The base currency "GBP" is not supported by the API "currencybeacon"');

        $client->getTimeSeries(
            new Carbon('2023-01-01'),
            new Carbon('2023-01-02'),
            'GBP',
            ['EUR']
        );
    }

    public function testVerifyCurrencies()
    {
        $expectedResponse = [
            'response' => [
                ['short_code' => 'USD', 'name' => 'United States Dollar'],
                ['short_code' => 'EUR', 'name' => 'Euro'],
                ['short_code' => 'HUF', 'name' => 'Hungarian Forint'],
            ],
        ];

        Http::fake([
            'api.currencybeacon.com/v1/currencies*' => Http::response($expectedResponse, 200),
            'api.currencybeacon.com/v1/timeseries*' => Http::response(['response' => []], 200),
        ]);

        $client = CurrencyExchangeRateApiClientFactory::create('currencybeacon');
        $client->clearCacheForKey('supported_currencies');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The currency "GBP" is not supported by the API "currencybeacon"');

        $client->getTimeSeries(
            new Carbon('2023-01-01'),
            new Carbon('2023-01-02'),
            'USD',
            ['EUR', 'GBP']
        );
    }

    public function testIsCurrencySupported()
    {
        $expectedResponse = [
            'response' => [
                ['short_code' => 'USD', 'name' => 'United States Dollar'],
                ['short_code' => 'EUR', 'name' => 'Euro'],
                ['short_code' => 'HUF', 'name' => 'Hungarian Forint'],
            ],
        ];

        Http::fake([
            'api.currencybeacon.com/v1/currencies*' => Http::response($expectedResponse, 200),
        ]);

        $client = CurrencyExchangeRateApiClientFactory::create('currencybeacon');
        $client->clearCacheForKey('supported_currencies');

        $this->assertTrue($client->isCurrencySupported('USD'));
        $this->assertTrue($client->isCurrencySupported('EUR'));
        $this->assertFalse($client->isCurrencySupported('GBP'));
    }
}
