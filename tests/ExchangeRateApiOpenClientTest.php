<?php

namespace Kantorge\CurrencyExchangeRates\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Kantorge\CurrencyExchangeRates\CurrencyExchangeRateApiClientFactory;

class ExchangeRateApiOpenClientTest extends TestCase
{
    public function testGetSupportedCurrencies()
    {
        $expectedResponse = [
            'result' => 'success',
            'supported_codes' => [
                ['USD', 'United States Dollar'],
                ['EUR', 'Euro'],
                ['HUF', 'Hungarian Forint'],
                ['GBP', 'British Pound Sterling'],
            ],
        ];

        Http::fake([
            'open.er-api.com/v6/codes' => Http::response(
                $expectedResponse,
                200,
                []
            ),
        ]);

        $client = CurrencyExchangeRateApiClientFactory::create('exchangerate-api-open');

        // Clear the cache before the test runs
        Cache::forget($client->clearCacheForKey('supported_currencies'));

        $currencies = $client->getSupportedCurrencies();

        $this->assertIsArray($currencies);
        $this->assertEquals(['USD', 'EUR', 'HUF', 'GBP'], $currencies);
        Http::assertSentCount(1);

        // Test if a second call to the API is reading from the cache
        $currencies = $client->getSupportedCurrencies();
        Http::assertSentCount(1);
    }

    public function testGetTimeSeries()
    {
        // Mock responses for two consecutive days
        $day1Response = [
            'result' => 'success',
            'base_code' => 'USD',
            'rates' => [
                'EUR' => 0.85,
                'HUF' => 350.5,
                'GBP' => 0.75,
            ],
        ];

        $day2Response = [
            'result' => 'success',
            'base_code' => 'USD',
            'rates' => [
                'EUR' => 0.86,
                'HUF' => 351.0,
                'GBP' => 0.76,
            ],
        ];

        Http::fake([
            'open.er-api.com/v6/history/USD/2023/01/01' => Http::response($day1Response, 200, []),
            'open.er-api.com/v6/history/USD/2023/01/02' => Http::response($day2Response, 200, []),
        ]);

        $client = CurrencyExchangeRateApiClientFactory::create('exchangerate-api-open');

        // Clear the cache before the test runs
        $cacheKey = 'time_series_2023-01-01_2023-01-02_USD_EUR_HUF';
        Cache::forget($client->clearCacheForKey($cacheKey));

        $timeSeries = $client->getTimeSeries(
            new Carbon('2023-01-01'),
            new Carbon('2023-01-02'),
            'USD',
            ['EUR', 'HUF']
        );

        $this->assertIsArray($timeSeries);
        $this->assertArrayHasKey('2023-01-01', $timeSeries);
        $this->assertArrayHasKey('2023-01-02', $timeSeries);
        
        // Check that the correct currencies are in the response
        $this->assertArrayHasKey('EUR', $timeSeries['2023-01-01']);
        $this->assertArrayHasKey('HUF', $timeSeries['2023-01-01']);
        $this->assertArrayNotHasKey('GBP', $timeSeries['2023-01-01']);
        
        // Check the actual values
        $this->assertEquals(0.85, $timeSeries['2023-01-01']['EUR']);
        $this->assertEquals(350.5, $timeSeries['2023-01-01']['HUF']);
        $this->assertEquals(0.86, $timeSeries['2023-01-02']['EUR']);
        $this->assertEquals(351.0, $timeSeries['2023-01-02']['HUF']);

        // Should have made 2 API calls (one for each day)
        Http::assertSentCount(2);

        // Test if a second call to the API is reading from the cache
        $timeSeries = $client->getTimeSeries(
            new Carbon('2023-01-01'),
            new Carbon('2023-01-02'),
            'USD',
            ['EUR', 'HUF']
        );
        Http::assertSentCount(2);
    }

    public function testGetTimeSeriesSingleDay()
    {
        $dayResponse = [
            'result' => 'success',
            'base_code' => 'EUR',
            'rates' => [
                'USD' => 1.18,
                'HUF' => 380.0,
            ],
        ];

        Http::fake([
            'open.er-api.com/v6/history/EUR/2023/06/15' => Http::response($dayResponse, 200, []),
        ]);

        $client = CurrencyExchangeRateApiClientFactory::create('exchangerate-api-open');

        $timeSeries = $client->getTimeSeries(
            new Carbon('2023-06-15'),
            new Carbon('2023-06-15'),
            'EUR',
            ['USD', 'HUF']
        );

        $this->assertIsArray($timeSeries);
        $this->assertArrayHasKey('2023-06-15', $timeSeries);
        $this->assertEquals(1.18, $timeSeries['2023-06-15']['USD']);
        $this->assertEquals(380.0, $timeSeries['2023-06-15']['HUF']);

        // Should have made 1 API call
        Http::assertSentCount(1);
    }

    public function testIsCurrencySupported()
    {
        $expectedResponse = [
            'result' => 'success',
            'supported_codes' => [
                ['USD', 'United States Dollar'],
                ['EUR', 'Euro'],
                ['HUF', 'Hungarian Forint'],
            ],
        ];

        Http::fake([
            'open.er-api.com/v6/codes' => Http::response($expectedResponse, 200, []),
        ]);

        $client = CurrencyExchangeRateApiClientFactory::create('exchangerate-api-open');

        // Clear the cache before the test runs
        Cache::forget($client->clearCacheForKey('supported_currencies'));

        $this->assertTrue($client->isCurrencySupported('USD'));
        $this->assertTrue($client->isCurrencySupported('EUR'));
        $this->assertTrue($client->isCurrencySupported('HUF'));
        $this->assertFalse($client->isCurrencySupported('GBP'));
        $this->assertFalse($client->isCurrencySupported('INVALID'));
    }
}
