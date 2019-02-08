<?php

namespace Ibericode\Vat\Tests;

use Ibericode\Vat\Clients\JsonVat;
use Ibericode\Vat\Period;
use Ibericode\Vat\Vat;
use PHPUnit\Framework\TestCase;

class VatTest extends TestCase
{
    public function testGetCountry()
    {
        $client = $this->getMockBuilder(JsonVat::class)->getMock();
        $client
            ->method('fetch')
            ->willReturn($this->getSampleJsonVatData());

        $vat = new Vat(null, $client);
        $country = $vat->getCountry('NL');

        $this->assertEquals('NL', $country->getCode());
        $this->assertEquals('Netherlands', $country->getName());
        $this->assertEquals(23.0, $country->getRate());
        $this->assertEquals(22.0, $country->getRateOn(new \DateTime('2016/01/01')));
    }

    public function getSampleJsonVatData()
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