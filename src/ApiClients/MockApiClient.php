<?php

namespace Kantorge\CurrencyExchangeRates\ApiClients;

class MockApiClient extends BaseCurrencyClient implements ExchangeRateApiClientInterface
{
    public const IDENTIFIER = 'mock';

    public function getTimeSeries($startDate, $endDate, $baseCurrency, array $currencies)
    {
        $this->verifyTimeSeriesArguments($startDate, $endDate, $baseCurrency, $currencies);

        // Return mock data for the given parameters, using the helper function
        $data = $this->getMockTimeSeriesData();

        // Filter the data for the given start and end dates
        $data = array_filter($data, function ($date) use ($startDate, $endDate) {
            return $date >= $startDate->format('Y-m-d') && $date <= $endDate->format('Y-m-d');
        }, ARRAY_FILTER_USE_KEY);

        // Filter the data for the given base currency
        $data = array_map(function ($rates) use ($baseCurrency) {
            return $rates[$baseCurrency];
        }, $data);

        // Filter the data for the given currencies
        $data = array_map(function ($rates) use ($currencies) {
            return array_filter($rates, function ($currency) use ($currencies) {
                return in_array($currency, $currencies);
            }, ARRAY_FILTER_USE_KEY);
        }, $data);

        return $data;
    }

    public function getSupportedCurrencies(): array
    {
        // Return mock data
        return [
            'USD',
            'EUR',
            'HUF',
        ];
    }

    /**
     * Define the mock data for the time series API call.
     */
    protected function getMockTimeSeriesData(): array
    {
        // TODO: extend the values to one month
        // TODO: add actual historical data
        return [
            '2023-01-01' => [
                'USD' => [
                    'EUR' => 0.9,
                    'HUF' => 0.8,
                ],
                'EUR' => [
                    'USD' => 1.1,
                    'HUF' => 0.9,
                ],
                'HUF' => [
                    'USD' => 1.2,
                    'EUR' => 1.1,
                ],
            ],
            '2023-01-02' => [
                'USD' => [
                    'EUR' => 0.8,
                    'HUF' => 0.7,
                ],
                'EUR' => [
                    'USD' => 1.2,
                    'HUF' => 1.0,
                ],
                'HUF' => [
                    'USD' => 1.3,
                    'EUR' => 1.2,
                ],
            ],
            '2023-01-03' => [
                'USD' => [
                    'EUR' => 0.7,
                    'HUF' => 0.6,
                ],
                'EUR' => [
                    'USD' => 1.3,
                    'HUF' => 1.1,
                ],
                'HUF' => [
                    'USD' => 1.4,
                    'EUR' => 1.3,
                ],
            ],
        ];
    }
}