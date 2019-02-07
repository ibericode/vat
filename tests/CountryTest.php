<?php

namespace Ibericode\Vat\Tests;

use Ibericode\Vat\Country;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    public function testGetName()
    {
        $country = new Country('NL', 'Netherlands', []);
        $this->assertEquals('Netherlands', $country->getName());
    }

    public function testGetCode()
    {
        $country = new Country('NL', 'Netherlands', []);
        $this->assertEquals('NL', $country->getCode());
    }

    public function testGetRate()
    {
        $country = new Country('NL', 'Netherlands', [
            [
                'effective_from' => new \DateTime('2015/01/01'),
                'rates' => [
                    'standard' => 21.00,
                ]
            ]
        ]);

        $rate = $country->getRate();
        $this->assertEquals(21.00, $rate);
    }

    public function testGetRateOn()
    {
        $country = new Country('NL', 'Netherlands', [
            [
                'effective_from' => new \DateTime('2015/01/01'),
                'rates' => [
                    'standard' => 21.00,
                ]
            ],
            [
                'effective_from' => new \DateTime('2016/01/01'),
                'rates' => [
                    'standard' => 22.00,
                ]
            ],
            [
                'effective_from' => new \DateTime('2017/01/01'),
                'rates' => [
                    'standard' => 23.00,
                ]
            ]
        ]);

        $rate = $country->getRateOn(new \DateTime('2016/02/01'));
        $this->assertEquals(22.00, $rate);
    }

    public function testIsEU()
    {
        $country = new Country('NL', 'Netherlands', []);
        $this->assertTrue($country->isEU());

        $country = new Country('US', 'United States', []);
        $this->assertFalse($country->isEU());
    }
}