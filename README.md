vat
================

[![Build Status](https://img.shields.io/travis/dannyvankooten/vat.php.svg)](https://travis-ci.org/dannyvankooten/vat.php)
[![Latest Stable Version](https://img.shields.io/packagist/v/dannyvankooten/vat.php.svg)](https://packagist.org/packages/dannyvankooten/vat.php)
![PHP from Packagist](https://img.shields.io/packagist/php-v/dannyvankooten/vat.php.svg)
![Total Downloads](https://img.shields.io/packagist/dt/dannyvankooten/vat.php.svg)
![License](https://img.shields.io/github/license/dannyvankooten/vat.php.svg)

This is a simple PHP library to help you deal with Europe's VAT rules. 

- Fetch (historical) VAT rates for any EU member state using [jsonvat.com](https://github.com/adamcooke/vat-rates)
- Validate VAT numbers (by format, [existence](http://ec.europa.eu/taxation_customs/vies/) or both)
- Validate ISO-3316 alpha-2 country codes
- Determine whether a country is part of the EU
- Geo-locate IP addresses

The library uses jsonvat.com to obtain its data for the VAT rates. Full details can be seen [here](https://github.com/adamcooke/vat-rates).
 To verify the validity of a VAT number, [VIES VAT number validation](http://ec.europa.eu/taxation_customs/vies/) is used.

## Installation

[PHP](https://php.net) version 7.1 or higher is required. For VAT number existence checking, the PHP SOAP extension is required as well.

To get the latest version of vat.php, install the package using [Composer](https://getcomposer.org):

```bash
$ composer require dannyvankooten/vat.php
```

## Usage

This library exposes 3 main classes to interact with, `Rates`, `Countries` and `Validator`.

#### Fetching VAT rates.

```php
$rates = new DvK\Vat\Rates\Rates();
$rates->country('NL'); // 21
$rates->country('NL', 'standard'); // 21
$rates->country('NL', 'standard', new \Datetime('2010-01-01')); // 19
$rates->country('NL', 'reduced'); // 6
$rates->all(); // array in country code => rates format
```

#### Validating a VAT number

```php
$validator = new DvK\Vat\Validator();
$validator->validate('NL50123'); // false
$validator->validateFormat('NL203458239B01'); // true (checks format)
$validator->validateExistence('NL203458239B01'); // false (checks existence)
$validator->validate('NL203458239B01'); // false (checks format + existence)
```


#### Dealing with countries & geolocation

```php
$countries = new DvK\Vat\Countries();
$countries->all(); // array of country codes + names
$countries->name('NL') // Netherlands
$countries->europe(); // array of EU country codes + names
$countries->inEurope('NL'); // true
$countries->ip('8.8.8.8'); // US
```

#### Symfony support

If you need to use this package in a Symfony environment, check out [ibericode/vat-bundle](https://github.com/ibericode/vat-bundle).

## License

vat.php is licensed under the [MIT License](LICENSE).
