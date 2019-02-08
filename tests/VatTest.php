<?php

namespace Ibericode\Vat\Tests;

use Ibericode\Vat\Cache\NullCache;
use Ibericode\Vat\Clients\JsonVat;
use Ibericode\Vat\Exceptions\Exception;
use Ibericode\Vat\Period;
use Ibericode\Vat\Rates;
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

    public function testGetRateForCountry()
    {
        $client = $this->getJsonVatMock();
        $vat = new Rates(null, $client);
        $rate = $vat->getRateForCountry('NL');
        $this->assertEquals(23.0, $rate);
    }

    public function testGetRateForCountryWithInvalidCountryCode()
    {
        $client = $this->getJsonVatMock();
        $vat = new Rates(null, $client);
        $this->expectException(Exception::class);
        $vat->getRateForCountry('FOO');
    }

    public function testRatesAreCached()
    {
        $cache = $this->getMockBuilder(NullCache::class)->getMock();
        $cache
            ->method('has')
            ->willReturn(true);
        $cache
            ->method('get')
            ->willReturn([
                'NL' => [
                    new Period(new \DateTime('2015/01/01'), [
                        'standard' => 25.00,
                    ])
                ]
            ]);

        $client = $this->getJsonVatMock();
        $client->method('fetch')->willThrowException(new \Exception('fetch() called while trying to get from cache'));
        $vat = new Rates($cache, $client);

        $rate = $vat->getRateForCountry('NL');
        $this->assertEquals(25.0, $rate);
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