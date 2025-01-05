<?php

namespace Ibericode\Vat\Tests;

use Ibericode\Vat\Geolocator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GeolocatorTest extends TestCase
{
    /**
     * @group remote-http
     */
    #[DataProvider('servicesProvider')]
    public function testService($service): void
    {
        $geolocator = new Geolocator($service);
        $country = $geolocator->locateIpAddress('8.8.8.8');
        $this->assertEquals('US', $country);
    }

    public static function servicesProvider(): \Generator
    {
        yield ['ip2c.org'];
    }
}
