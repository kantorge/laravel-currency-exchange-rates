# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
composer install          # install dependencies
composer test              # run tests (vendor/bin/pest)
vendor/bin/pest --filter=Frankfurter   # run a single test file/case
composer test-coverage     # run tests with coverage
composer analyse            # PHPStan level 5 (src/ and config/)
composer format              # Laravel Pint (auto-fixes style)
```

There is no separate lint step beyond Pint/PHPStan above. `.github/copilot-instructions.md` has more detail on CI workflows and troubleshooting composer install issues if needed.

## Architecture

This is a Laravel package (not an application) providing a unified interface over multiple currency exchange rate data sources.

- `ExchangeRateApiClientInterface` (`src/ApiClients/`) — the contract every source implements: `getTimeSeries()`, `getSupportedCurrencies()`, `isCurrencySupported()`, `getBaseUrl()`, `clearCacheForKey()`.
- `BaseCurrencyClient` — abstract base all clients extend. Holds shared behavior: HTTP requests via `makeApiRequest()` (Guzzle through Laravel's `Http` facade), cache key building (`{cache_prefix}_{IDENTIFIER}_{key}`), and argument validation (`verifyBaseCurrency`, `verifyCurrencies`, `verifyDates`, `verifyTimeSeriesArguments`).
- Concrete clients (`FrankfurterApiClient`, `CurrencyBeaconApiClient`, `MockApiClient`) each define a `const IDENTIFIER`, a `$baseUrl`, and implement the two abstract-required methods, using `Cache::remember()` keyed via `getCacheKey()` with a per-provider TTL from config.
- `CurrencyExchangeRateApiClientFactory::create(?string $type)` — the only intended way to instantiate a client. Falls back to `config('currency-exchange-rates.default_provider')` when no type is given; throws on an unknown identifier. Registered in the container as `currency-exchange-rates` by `CurrencyExchangeRatesServiceProvider`, exposed via the `CurrencyExchangeRates` facade.
- `config/currency-exchange-rates.php` — `default_provider`, `cache_prefix`, and per-provider settings (API keys, `cache_ttl`).

**Adding a new API client**: create a class in `src/ApiClients/` extending `BaseCurrencyClient`, define a unique `IDENTIFIER` and `$baseUrl`, implement `getTimeSeries()`/`getSupportedCurrencies()` (using the inherited cache/validation helpers), add a `case` to the factory's switch, add a config block if it needs settings, and add a test class mirroring the existing `*ApiClientTest.php` files (they mock HTTP with `Http::fake()` — see `FrankfurterApiClientTest.php`).

Tests run against a minimal Laravel app via Orchestra Testbench (`tests/TestCase.php`); `workbench/` is the local dev sandbox used by `composer start`.
