<?php

namespace Ibericode\Vat\Tests;

use Ibericode\Vat\Countries;
use Ibericode\Vat\Exception;
use PHPUnit\Framework\TestCase;

class CountriesTest extends TestCase
{
    public function testIterator()
    {
        $countries = new Countries();

        $this->assertCount(245, $countries);
    }

    public function testArrayAccess()
    {
        $countries = new Countries();

        $this->assertEquals('Afghanistan', $countries['AF']);
        $this->assertEquals('Netherlands', $countries['NL']);

        $this->expectException(Exception::class);
        $countries['FOO'];
    }

    public function testArrayAccessWithInvalidCountryCode()
    {
        $countries = new Countries();
        $this->expectException(Exception::class);
        $countries['FOO'];
    }

    public function testArrayAccessSetValue()
    {
        $countries = new Countries();
        $this->expectException(Exception::class);
        $countries['FOO'] = 'bar';
    }

    public function testArrayAccessUnsetValue()
    {
        $countries = new Countries();
        $this->expectException(Exception::class);
        unset($countries['FOO']);
    }

    public function testHasCode()
    {
        $countries = new Countries();
        $this->assertFalse($countries->hasCountryCode('FOO'));
        $this->assertTrue($countries->hasCountryCode('NL'));
    }

    public function testIsCodeInEU()
    {
        $countries = new Countries();
        $this->assertFalse($countries->isCountryCodeInEU('FOO'));
        $this->assertFalse($countries->isCountryCodeInEU('US'));
        $this->assertTrue($countries->isCountryCodeInEU('NL'));
    }
}
