<?php

namespace Ibericode\Vat\Tests;

use Ibericode\Vat\Countries;
use PHPUnit\Framework\TestCase;

class CountriesTest extends TestCase
{
    public function testGetWithInvalidCode()
    {
        $countries = new Countries();
        $this->expectExceptionMessage('Invalid country code');
        $countries->get('FOO');
    }

    public function testGetWithValidCode()
    {
        $countries = new Countries();
        $nl = $countries->get('NL');
        $this->assertEquals('NL', $nl->getCode());
        $this->assertEquals('Netherlands', $nl->getName());
        $this->assertTrue($nl->isEU());

        $us = $countries->get('US');
        $this->assertEquals('US', $us->getCode());
        $this->assertEquals('United States', $us->getName());
        $this->assertFalse($us->isEU());
    }
}
