<?php

namespace Ibericode\Vat\Tests;

use Ibericode\Vat\Cache\NullCache;
use Ibericode\Vat\Clients\JsonVat;
use Ibericode\Vat\Exceptions\Exception;
use Ibericode\Vat\Period;
use Ibericode\Vat\Vat;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class VatTest extends TestCase
{
    private function getJsonVatMock()
    {
        $client = $this->getMockBuilder(JsonVat::class)->getMock();
        $client
            ->method('fetch')
            ->willReturn($this->getSampleJsonVatData());

        return $client;
    }

    public function testGetCountry()
    {
        $client = $this->getJsonVatMock();
        $vat = new Vat(null, $client);
        $country = $vat->getCountry('NL');

        $this->assertEquals('NL', $country->getCode());
        $this->assertEquals('Netherlands', $country->getName());
        $this->assertEquals(23.0, $country->getRate());
        $this->assertEquals(22.0, $country->getRateOn(new \DateTime('2016/01/01')));
    }

    public function testGetCountryWithInvalidCountryCode()
    {
        $client = $this->getJsonVatMock();
        $vat = new Vat(null, $client);
        $this->expectException(Exception::class);
        $vat->getCountry('FOO');
    }

    public function testRatesAreCached()
    {
        $cache = $this->getMockBuilder(NullCache::class)->getMock();
        $cache
            ->method('has')
            ->willReturn(true);
        $cache
            ->method('get')
            ->willReturn($this->getSampleJsonVatData());

        $client = $this->getJsonVatMock();
        $client->method('fetch')->willThrowException(new \Exception('fetch() called while trying to get from cache'));
        $vat = new Vat($cache, $client);

        $country = $vat->getCountry('NL');
        $this->assertEquals(23.0, $country->getRate());
    }


    private function getSampleJsonVatData()
    {
        return [
           'NL' => [
               new Period(new \DateTime('2015/01/01'), [
                   'standard' => 21.00,
               ]),
               new Period(new \DateTime('2016/01/01'), [
                   'standard' => 22.00,
               ]),
               new Period(new \DateTime('2017/01/01'), [
                   'standard' => 23.00,
               ])
           ]
        ];
    }
}