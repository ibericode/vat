<?php

namespace DvK\Tests\Vat;

use DvK\Vat\Countries;
use PHPUnit\Framework\TestCase;

/**
 * Class CountriesTest
 * @package DvK\Tests\Vat
 *
 */
class CountriesTest extends TestCase
{
    /**
     * @covers Countries::name
     */
    public function testName() {
        $countries = new Countries();
        self::assertEquals( 'United States', $countries->name('US'));
    }
    /**
     * @covers Countries::inEurope
     */
    public function testInEurope() {
        $countries = new Countries();
        $invalid = [ 'US', '', 'NE', 'JP', 'RU' ];
        foreach( $invalid as $country ) {
            self::assertFalse( $countries->inEurope( $country ) );
        }
        $valid = [ 'NL', 'nl', 'GB', 'GR', 'BE' ];
        foreach( $valid as $country ) {
            self::assertTrue( $countries->inEurope( $country ) );
        }
    }

    /**
     * @covers Countries::validateIpAddress
     */
    public function testValidateIpAddress() {
        $countries = new Countries();
        $map = [
            'foo' => false,
            '192.168.1.10' => false,
            '8.8.8.8' => true,
            '54.18.12.111' => true,
        ];

        foreach($map as $ip => $expected) {
            self::assertEquals($expected, $countries->validateIpAddress($ip));
        }
    }

     /**
     * @covers Countries::ip
     */
    public function testIp() {
        $countries = new Countries();
        self::assertEmpty($countries->ip(''));
    }
    
}
