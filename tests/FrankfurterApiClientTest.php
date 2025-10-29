<?php

namespace Kantorge\CurrencyExchangeRates\Tests;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Kantorge\CurrencyExchangeRates\CurrencyExchangeRateApiClientFactory;

class FrankfurterApiClientTest extends TestCase
{
    public function test_get_supported_currencies()
    {
        $expectedCurrencies = [
            'USD' => 'United States Dollar',
            'EUR' => 'Euro',
            'HUF' => 'Hungarian Forint',
        ];

        $client = CurrencyExchangeRateApiClientFactory::create('frankfurter');
        $baseUrl = $client->getBaseUrl();

        Http::fake([
            $baseUrl.'/currencies' => Http::response(
                $expectedCurrencies,
                200,
                []
            ),
        ]);

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
