<?php

namespace Ibericode\Vat\Tests;

use Ibericode\Vat\Validator;

use PHPUnit\Framework\TestCase;

/**
 * Class ValidatorTest
 * @package DvK\Tests\Vat
 */
class ValidatorTest extends TestCase
{

    /**
     * @covers Validator::validateVatNumberFormat
     */
    public function testValidateVatNumberFormat()
    {
        $valid = [
            'ATU12345678',
            'BE0123456789',
            'BE1234567891',
            'BG123456789',
            'BG1234567890',
            'CY12345678X',
            'CZ12345678',
            'DE123456789',
            'DK12345678',
            'EE123456789',
            'EL123456789',
            'ESX12345678',
            'FI12345678',
            'FR12345678901',
            'FRA2345678901',
            'FRAB345678901',
            'FR1B345678901',
            'GB999999973',
            'HU12345678',
            'HR12345678901',
            'IE1234567X',
            'IT12345678901',
            'LT123456789',
            'LU12345678',
            'LV12345678901',
            'MT12345678',
            'NL123456789B12',
            'PL1234567890',
            'PT123456789',
            'RO123456789',
            'SE123456789012',
            'SI12345678',
            'SK1234567890',
        ];

        $validator = new Validator();
        foreach ($valid as $format) {
            $this->assertTrue($validator->validateVatNumberFormat($format), "{$format} did not pass validation.");
        }

        $invalid = [
            '',
            'ATU1234567',
            'BE012345678',
            'BE123456789',
            'BG1234567',
            'CY1234567X',
            'CZ1234567',
            'DE12345678',
            'DK1234567',
            'EE12345678',
            'EL12345678',
            'ESX1234567',
            'FI1234567',
            'FR1234567890',
            'GB99999997',
            'HU1234567',
            'HR1234567890',
            'IE123456X',
            'IT1234567890',
            'LT12345678',
            'LU1234567',
            'LV1234567890',
            'MT1234567',
            'NL12345678B12',
            'PL123456789',
            'PT12345678',
            'RO1',  // Romania has a really weird VAT format...
            'SE12345678901',
            'SI1234567',
            'SK123456789',

            // valid number but with prefix
            'invalid_prefix_GB999999973',
            'invalid_prefix_IE1234567X',
            'invalid_prefix_ESB1234567C',
            'invalid_prefix_BE0123456789',
            'invalid_prefix_MT12345678',
            'invalid_prefix_LT123456789',

            // valid number but with suffix
            'IE1234567X_invalid_suffix',
            'ESB1234567C_invalid_suffix',
            'BE0123456789_invalid_suffix',
            'MT12345678_invalid_suffix',
            'LT123456789_invalid_suffix',
        ];

        foreach ($invalid as $format) {
            $isValid = $validator->validateVatNumberFormat($format);
            $this->assertFalse($isValid, "{$format} passed validation, but shouldn't.");
        }
    }

    /**
     * @dataProvider validIpAddresses
     */
    public function testValidateIpAddressWithValid($value)
    {
        $validator = new Validator();
        $this->assertTrue($validator->validateIpAddress($value));
    }

    public function validIpAddresses()
    {
        return [
            ['8.8.8.8'],
            ['54.18.12.111']
        ];
    }

    /**
     * @dataProvider invalidIpAddresses
     */
    public function testValidateIpAddressWithInvalidValues($value)
    {
        $validator = new Validator();
        $this->assertFalse($validator->validateIpAddress($value));
    }

    public function invalidIpAddresses()
    {
        return [
            ['0.8.8.8.8'],
            ['foo.bar'],
            ['192.168.1.10'], // local range
        ];
    }

    /**
     * @dataProvider validCountryCodes
     */
    public function testValidateCountryCodeWithValidValues($value)
    {
        $validator = new Validator();
        $this->assertTrue($validator->validateCountryCode($value));
    }

    /**
     * @dataProvider invalidCountryCodes
     */
    public function testValidateCountryCodeWithInvalidValues($value)
    {
        $validator = new Validator();
        $this->assertFalse($validator->validateCountryCode($value));
    }


    public function validCountryCodes()
    {
        return [
           ['NL'],
           ['DE'],
           ['US'],
           ['GB'],
        ];
    }

    public function invalidCountryCodes()
    {
        return [
            ['FOO'],
            ['false'],
            ['null'],
            ['0'],
            ['nl'],
        ];
    }
}
