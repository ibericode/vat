<?php

namespace DvK\Tests\Vat;

use DvK\Vat\Countries;
use PHPUnit_Framework_TestCase;

/**
 * Class CountriesTest
 * @package DvK\Tests\Vat
 *
 */
class CountriesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Countries::name
     */
    public function test_name() {
        $countries = new Countries();
        self::assertEquals( 'United States', $countries->name('US'));
    }
    /**
     * @covers Countries::inEurope
     */
    public function test_inEurope() {
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
    
}
