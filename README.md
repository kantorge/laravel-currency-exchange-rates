# A Laravel package to retrieve historical currency exchange rate data

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kantorge/laravel-currency-exchange-rates.svg?style=flat-square)](https://packagist.org/packages/kantorge/laravel-currency-exchange-rates)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/kantorge/laravel-currency-exchange-rates/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/kantorge/laravel-currency-exchange-rates/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/kantorge/laravel-currency-exchange-rates/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/kantorge/laravel-currency-exchange-rates/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/kantorge/laravel-currency-exchange-rates.svg?style=flat-square)](https://packagist.org/packages/kantorge/laravel-currency-exchange-rates)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require kantorge/laravel-currency-exchange-rates
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-currency-exchange-rates-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$currencyExchangeRates = new Kantorge\CurrencyExchangeRates();
echo $currencyExchangeRates->echoPhrase('Hello, Kantorge!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [kantorge](https://github.com/kantorge)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
