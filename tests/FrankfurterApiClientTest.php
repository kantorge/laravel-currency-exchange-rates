<?php

namespace Kantorge\CurrencyExchangeRates\Tests;

use Kantorge\CurrencyExchangeRates\CurrencyExchangeRateApiClientFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class FrankfurterApiClientTest extends TestCase
{
    public function testGetSupportedCurrencies()
    {
        $expectedCurrencies = [
            'USD' => 'United States Dollar',
            'EUR' => 'Euro',
            'HUF' => 'Hungarian Forint',
        ];

        Http::fake([
            'api.frankfurter.app/currencies' => Http::response(
                $expectedCurrencies,
                200,
                []
            ),
        ]);

        $client = CurrencyExchangeRateApiClientFactory::create('frankfurter');

        // Clear the cache before the test runs
        Cache::forget($client->clearCacheForKey('supported_currencies'));

        $currencies = $client->getSupportedCurrencies();

        $this->assertIsArray($currencies);
        $this->assertEquals(array_keys($expectedCurrencies), $currencies);
        Http::assertSentCount(1);

        // Test if a second call to the API is reading from the cache
        $currencies = $client->getSupportedCurrencies();
        Http::assertSentCount(1);
    }
}