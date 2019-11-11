<?php

namespace Ibericode\Vat\Tests;

use Ibericode\Vat\Geolocator;
use PHPUnit\Framework\TestCase;

class GeolocatorTest extends TestCase
{
    /**
     * @dataProvider provider
     * @group remote-http
     */
    public function testClient($service)
    {
        $geolocator = new Geolocator($service);
        $country = $geolocator->locateIpAddress('8.8.8.8');
        $this->assertEquals('US', $country);
    }

    public function provider()
    {
        yield ['ip2c.org'];
        yield ['ip2country.info'];
    }
}
