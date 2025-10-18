# Copilot Instructions for Laravel Currency Exchange Rates

## Repository Overview

This is a **Laravel package** that provides a unified interface for retrieving historical currency exchange rate data from various sources (currently Frankfurter API and a Mock provider for testing). The package is written in **PHP 8.1+** using the **Laravel Framework 10.x** and follows Laravel package development conventions.

**Repository Statistics:**
- Language: PHP
- Framework: Laravel 10.x
- Testing: Pest (PHP testing framework)
- Static Analysis: PHPStan (level 5)
- Code Style: Laravel Pint
- Size: Small (~21 PHP files)
- Type: Composer package/Laravel service provider

## Project Structure

```
.
├── src/                          # Main source code
│   ├── ApiClients/              # API client implementations
│   │   ├── BaseCurrencyClient.php          # Abstract base class for all clients
│   │   ├── ExchangeRateApiClientInterface.php  # Interface all clients must implement
│   │   ├── FrankfurterApiClient.php        # Frankfurter API implementation
│   │   └── MockApiClient.php               # Mock implementation for testing
│   ├── Facades/                 # Laravel facades
│   │   └── CurrencyExchangeRates.php
│   ├── CurrencyExchangeRateApiClientFactory.php  # Factory for creating API clients
│   └── CurrencyExchangeRatesServiceProvider.php  # Laravel service provider
├── config/
│   └── currency-exchange-rates.php  # Package configuration file
├── tests/                       # Pest test files
│   ├── TestCase.php            # Base test case (extends Orchestra\Testbench)
│   ├── BaseApiClientTest.php   # Tests for base client functionality
│   ├── FrankfurterApiClientTest.php  # Tests for Frankfurter API
│   └── MockApiClientTest.php   # Tests for mock client
├── workbench/                   # Orchestra Workbench for local development
│   └── app/
├── composer.json                # PHP dependencies and scripts
├── phpunit.xml.dist            # PHPUnit/Pest configuration
├── phpstan.neon.dist           # PHPStan configuration (level 5)
├── phpstan-baseline.neon       # PHPStan baseline (currently empty)
└── .editorconfig               # Editor configuration
```

## Build and Test Process

### Prerequisites

- **PHP**: 8.1, 8.2, or 8.3
- **Composer**: 2.x
- **Extensions**: dom, curl, libxml, mbstring, zip, pdo, sqlite, pdo_sqlite, bcmath, soap, intl, gd, exif, iconv, fileinfo

### Installation

**ALWAYS run these commands in this order:**

```bash
# Clean install (recommended when starting fresh)
composer install --no-interaction

# If composer install fails with authentication errors, try:
composer install --no-interaction --prefer-source
```

**Common Issue**: Composer may fail with "Could not authenticate against github.com" errors when downloading packages from dist. This is normal in CI environments without GitHub tokens. The installation will automatically fall back to downloading from source (git clone), which takes longer but works without authentication.

**Expected Install Time**: 2-5 minutes (longer if falling back to source downloads)

### Running Tests

```bash
# Run all tests (uses Pest)
composer test
# OR directly:
vendor/bin/pest

# Run tests in CI mode (with stricter output)
vendor/bin/pest --ci

# Run tests with coverage
composer test-coverage
# OR:
vendor/bin/pest --coverage
```

**Expected Test Time**: Under 1 minute
**Test Framework**: Pest (built on PHPUnit 10.x)
**Test Location**: `tests/` directory

### Code Quality Tools

```bash
# Run static analysis (PHPStan level 5)
composer analyse
# OR:
vendor/bin/phpstan analyse

# Format code (Laravel Pint - automatic fix)
composer format
# OR:
vendor/bin/pint
```

**Expected PHPStan Time**: 10-30 seconds
**Expected Pint Time**: 5-15 seconds

### Other Commands

```bash
# Clear Laravel package cache
composer clear

# Prepare/discover package
composer prepare

# Build workbench
composer build

# Start development server (for testing in workbench)
composer start
```

## CI/CD Pipelines

The repository has 3 main CI workflows that run on push:

### 1. `run-tests.yml` (Tests)
- **Trigger**: Push to any PHP file, composer.json/lock, phpunit.xml.dist, or workflow file
- **Matrix**: PHP 8.1/8.2/8.3 × Laravel 10.* × prefer-stable/prefer-lowest × ubuntu-latest/windows-latest
- **Timeout**: 5 minutes
- **Steps**:
  1. Checkout code
  2. Setup PHP with required extensions
  3. Install dependencies: `composer require "laravel/framework:10.*" "orchestra/testbench:8.*" "nesbot/carbon:^2.63" --no-interaction --no-update && composer update --prefer-stable --prefer-dist --no-interaction`
  4. Run tests: `vendor/bin/pest --ci`

### 2. `phpstan.yml` (Static Analysis)
- **Trigger**: Push to any PHP file, phpstan.neon.dist, or workflow file
- **PHP Version**: 8.1
- **Timeout**: 5 minutes
- **Steps**:
  1. Checkout code
  2. Setup PHP 8.1
  3. Install dependencies via `ramsey/composer-install` action
  4. Run PHPStan: `./vendor/bin/phpstan --error-format=github`

### 3. `fix-php-code-style-issues.yml` (Auto-format)
- **Trigger**: Push to any PHP file
- **Timeout**: 5 minutes
- **Behavior**: Automatically fixes code style issues using Laravel Pint and commits them
- **Steps**:
  1. Checkout code
  2. Run Laravel Pint (via `aglipanci/laravel-pint-action`)
  3. Auto-commit style fixes with message "Fix styling"

### Important Notes:
- **Code style is auto-fixed**: Don't worry about formatting - Pint will fix it automatically after push
- **Tests must pass**: All test matrix combinations must pass
- **PHPStan must pass**: Level 5 static analysis must pass (baseline is empty)
- **All timeouts are 5 minutes**: If commands take longer, they will fail

## Validation Checklist

Before considering changes complete, ensure:

1. **Tests Pass**: `composer test` or `vendor/bin/pest --ci` succeeds
2. **PHPStan Passes**: `composer analyse` or `vendor/bin/phpstan analyse` shows no errors
3. **Code Style**: Run `composer format` or let CI auto-fix (style issues won't block PR)
4. **No Breaking Changes**: Existing tests still pass (unless intentionally changing behavior)

## Common Development Patterns

### Adding a New API Client

1. Create class in `src/ApiClients/` extending `BaseCurrencyClient`
2. Implement `ExchangeRateApiClientInterface` (getTimeSeries, getSupportedCurrencies, isCurrencySupported)
3. Define unique `IDENTIFIER` constant
4. Set `$baseUrl` property
5. Add to factory switch in `CurrencyExchangeRateApiClientFactory::create()`
6. Create test file in `tests/` (e.g., `YourClientTest.php`)
7. Add configuration in `config/currency-exchange-rates.php` if needed

### Testing with HTTP Mocks

The package uses Laravel's `Http::fake()` for mocking external API calls:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'api.example.com/endpoint' => Http::response(['data'], 200),
]);
```

See `FrankfurterApiClientTest.php` for examples.

### Cache Management

All API clients use Laravel's Cache facade with TTL from config:
- Cache keys follow pattern: `{cache_prefix}_{client_identifier}_{key}`
- Clear specific cache: `$client->clearCacheForKey('key_name')`
- Default cache prefix: `currency-exchange-rates`
- Default TTL: 60 minutes (configurable per provider)

## Key Architectural Decisions

1. **Factory Pattern**: Use `CurrencyExchangeRateApiClientFactory::create()` to instantiate clients
2. **Interface-Based**: All clients implement `ExchangeRateApiClientInterface`
3. **Base Class**: Common functionality in `BaseCurrencyClient` (caching, validation, HTTP requests)
4. **Laravel Integration**: Service provider, config publishing, facade support
5. **Orchestra Testbench**: Uses Orchestra Testbench for testing in a Laravel environment
6. **Carbon for Dates**: All date parameters use `Carbon\Carbon` instances

## Configuration Files

- **composer.json**: Defines PHP 8.1+ requirement, Guzzle 7.8+ dependency, autoloading, scripts
- **phpunit.xml.dist**: Pest/PHPUnit config (random execution order, strict mode, coverage to `build/`)
- **phpstan.neon.dist**: Level 5 static analysis, analyzes `src/` and `config/`
- **.editorconfig**: 4 spaces, LF line endings, UTF-8, trim trailing whitespace
- **.gitignore**: Excludes vendor/, build/, coverage/, composer.lock, phpunit.xml, phpstan.neon, etc.

## File Listing (Root Directory)

```
.editorconfig
.gitattributes
.gitignore
.release-please-manifest.json
CHANGELOG.md
LICENSE.md
README.md
composer.json
composer.lock (gitignored but generated during install)
config/
phpstan-baseline.neon
phpstan.neon.dist
phpunit.xml.dist
release-please-config.json
src/
tests/
workbench/
.github/
```

## Important Notes

1. **Trust These Instructions**: Only search for additional information if these instructions are incomplete or incorrect for your specific task.

2. **Composer Dependency Management**:
   - The package uses composer.lock for reproducible builds in CI
   - Always use `composer install` (not `composer update`) unless updating dependencies
   - CI uses `composer update` with version constraints to test against different dependency versions

3. **No Frontend**: This is a backend-only PHP package - no JavaScript, CSS, or views

4. **Laravel Package**: This is a reusable Laravel package, not a full Laravel application. It uses Orchestra Testbench for testing.

5. **External API Dependency**: The Frankfurter client makes real HTTP requests to https://api.frankfurter.app (mocked in tests)

6. **Semantic Versioning**: Uses release-please for automated releases and conventional commits

7. **PSR-4 Autoloading**: Namespace `Kantorge\CurrencyExchangeRates\` maps to `src/`

8. **Minimal TODOs**: Only 2 TODOs in codebase (both in MockApiClient.php for extending mock data)

9. **PHP Extensions**: The workflow specifies required extensions - if tests fail with "undefined function" errors, an extension may be missing

10. **Test Isolation**: Tests use Orchestra Testbench which provides a minimal Laravel application environment

## Troubleshooting

**Composer Install Fails with GitHub Auth Error**:
- Expected behavior in environments without GitHub token
- Solution: Wait for fallback to source downloads (takes longer but works)
- Alternative: Use `composer install --prefer-source` to skip dist downloads

**Tests Fail with "Class not found"**:
- Run `composer dump-autoload` to regenerate autoload files
- Ensure `composer install` completed successfully

**PHPStan Errors**:
- Check `phpstan-baseline.neon` (currently empty - no suppressed errors)
- Level is set to 5 (moderate strictness)
- Paths analyzed: `src/` and `config/`

**Pint Changes Not Applied**:
- Pint runs automatically in CI after push
- Run locally with `composer format` before push to see changes
- No Pint config file = uses Laravel Pint defaults
