ibericode/vat
================

[![Build Status](https://img.shields.io/travis/ibericode/vat.svg)](https://travis-ci.org/ibericode/vat)
[![Latest Stable Version](https://img.shields.io/packagist/v/ibericode/vat.svg)](https://packagist.org/packages/ibericode/vat)
![PHP from Packagist](https://img.shields.io/packagist/php-v/ibericode/vat.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/dannyvankooten/vat.php.svg)](https://packagist.org/packages/ibericode/vat)
![License](https://img.shields.io/github/license/ibericode/vat.svg)

This is a simple PHP library to help you deal with Europe's VAT rules. 

- Fetch VAT rates for any EU member state using [ibericode/vat-rates](https://github.com/ibericode/vat-rates).
- Validate VAT numbers (by format and/or [existence](http://ec.europa.eu/taxation_customs/vies/))
- Validate ISO-3316 alpha-2 country codes
- Determine whether a country is part of the EU
- Geo-locate IP addresses

## Installation

[PHP](https://php.net) version 7.1 or higher with the CURL and JSON extension is required. 

For VAT number existence checking, the PHP SOAP extension is required as well.

To get the latest stable version, install the package using [Composer](https://getcomposer.org):

```bash
composer require ibericode/vat
```

## Usage

This library exposes 4 main classes to interact with: `Rates`, `Countries`, `Validator` and `Geolocator`.

#### Retrieving VAT rates.

```php
$rates = new Ibericode\Vat\Rates('/path-for-storing-cache-file.txt');
$rates->getRateForCountry('NL'); // 21
$rates->getRateForCountry('NL', 'standard'); // 21
$rates->getRateForCountry('NL', 'reduced'); // 9
$rates->getRateForCountryOnDate('NL', new \Datetime('2010-01-01'), 'standard'); // 19
```

This fetches rate from [ibericode/vat-rates](https://github.com/ibericode/vat-rates) and stores a local copy that is periodically refreshed (once every 12 hours by default).

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
This library includes a simple geo-location service using [ip2c.org](https://about.ip2c.org/) or [ip2country.info](https://ip2country.info).

```php
$geolocator = new Ibericode\Vat\Geolocator();
$geolocator->locateIpAddress('8.8.8.8'); // US
```

To use ip2country.info explicitly.
```php
$geolocator = new Ibericode\Vat\Geolocator('ip2country.info');
$geolocator->locateIpAddress('8.8.8.8'); // US
```

Or, to use ip2c.org explicitly.

```php
$geolocator = new Ibericode\Vat\Geolocator('ip2c.org');
$geolocator->locateIpAddress('8.8.8.8'); // US
```

#### Symfony support

If you need to use this package in a Symfony environment, check out [ibericode/vat-bundle](https://github.com/ibericode/vat-bundle).

## License

ibericode/vat is licensed under the [MIT License](LICENSE).
