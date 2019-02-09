ibericode/vat
================

[![Build Status](https://img.shields.io/travis/ibericode/vat.svg)](https://travis-ci.org/ibericode/vat)
[![Latest Stable Version](https://img.shields.io/packagist/v/dannyvankooten/vat.php.svg)](https://packagist.org/packages/dannyvankooten/vat.php)
![PHP from Packagist](https://img.shields.io/packagist/php-v/dannyvankooten/vat.php.svg)
![Total Downloads](https://img.shields.io/packagist/dt/dannyvankooten/vat.php.svg)
![License](https://img.shields.io/github/license/ibericode/vat.svg)

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

#### Retrieving VAT rates.

```php
$rates = new Ibericode\Rates\Rates();
$rates->getRateForCountry('NL'); // 21
$rates->getRateForCountry('NL', 'standard'); // 21
$rates->getRateForCountry('NL', 'reduced'); // 9
$rates->getRateForCountryOn('NL', new \Datetime('2010-01-01'), 'standard'); // 19
```

#### Validation

Validating a VAT number:
```php
$validator = new Ibericode\Vat\Validator();
$validator->validateVatNumberFormat('NL203458239B01'); // true (checks format)
$validator->validateVatNumber('NL203458239B01'); // false (checks format + existence)
```

Validating an IP address:
```php
$validator = new Ibericode\Vat\Validator();
$validator->validateIpAddress('256.256.256.256'); // false
$validator->validateIpAddress('8.8.8.8'); // true
```

Validating an ISO-3166-1-alpha2 country code:
```php
$validator = new Ibericode\Vat\Validator();
$validator->validateCountryCode('DE'); // true
$validator->validateCountryCode('ZZ'); // false
```


#### Dealing with ISO-3166-1-alpha2 country codes

```php
$countries = new Ibericode\Vat\Countries();

// access country name using array access
echo $countries['NL']; // Netherlands

// loop over countries
foreach ($countries as $code => $name) {
    // ...
}

// check if country is in EU
$countries->isCountryCodeInEU('NL'); // true
$countries->isCountryCodeInEU('US'); // false
```

#### Geo-location
This library includes a simple geo-location service using ip2c.org.
```php
$geolocator = new Ibericode\Vat\Geolocator();
$geolocator->locateIpAddress('8.8.8.8'); // US
```

#### Symfony support

If you need to use this package in a Symfony environment, check out [ibericode/vat-bundle](https://github.com/ibericode/vat-bundle).

## License

ibericode/vat is licensed under the [MIT License](LICENSE).
