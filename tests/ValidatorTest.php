<?php

namespace DvK\Tests\Vat;

use DvK\Vat\Validator;
use DvK\Vat\Vies;

use PHPUnit_Framework_TestCase;

/**
 * Class ValidatorTest
 * @package DvK\Tests\Vat
 *
 * Todo: Tests for validate method
 */
class ValidatorTest extends PHPUnit_Framework_TestCase
{

    /**
     * @covers Validator::validateFormat
     */
    public function test_validateFormat() {
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
        foreach( $valid as $format ) {
            self::assertTrue( $validator->validateFormat( $format ), "{$format} did not pass validation." );
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
            'fooGB999999973', // valid VAT number but with string prefix
        ];

        foreach( $invalid as $format ) {
            $isValid = $validator->validateFormat( $format );
            self::assertFalse( $isValid, "{$format} passed validation, but shouldn't." );
        }
    }

    /**
     * @covers Validator::validateExistence
     */
    public function test_validateExistence() {
        $mock = self::getMockBuilder(Vies\Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects(self::once())
            ->method('checkVat')
            ->with('NL','123456789')
            ->will(self::returnValue(true));

        $validator = new Validator( $mock );
        self::assertTrue( $validator->validateExistence('NL123456789') );
    }

}
