vat.php
================

<a href="https://travis-ci.org/dannyvankooten/vat.php"><img src="https://img.shields.io/travis/dannyvankooten/vat.php/master.svg?style=flat-square" alt="Build Status"></img></a>
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square" alt="Software License"></img></a>

vat.php is a simple PHP library which helps you to deal with European VAT rules. It helps you...

- Grab up-to-date VAT rates for any European member state
- Validate VAT numbers (by format or existence)
- Work with ISO 3166-1 alpha-2 country codes and determine whether they're part of the EU.
- Geolocate IP addresses

The library uses jsonvat.com to obtain its data for the VAT rates. Full details can be seen [here](https://github.com/adamcooke/vat-rates).
For VAT number validation, it uses [VIES VAT number validation](http://ec.europa.eu/taxation_customs/vies/).

## Installation

[PHP](https://php.net) 5.6+ is required. For VAT number existence checking, the PHP SOAP extension is required as well.

To get the latest version of vat.php, simply require the project using [Composer](https://getcomposer.org):

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

## License

vat.php is licensed under [The MIT License (MIT)](LICENSE).
