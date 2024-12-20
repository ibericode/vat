<?php

namespace Ibericode\Vat\Tests;

use DateTime;
use Ibericode\Vat\Countries;
use Ibericode\Vat\Exception;
use PHPUnit\Framework\TestCase;

class CountriesTest extends TestCase
{
    public function testIterator(): void
    {
        $countries = new Countries();

        $this->assertCount(249, $countries);
    }

    public function testArrayAccess(): void
    {
        $countries = new Countries();

        $this->assertEquals('Afghanistan', $countries['AF']);
        $this->assertEquals('Netherlands', $countries['NL']);

        $this->expectException(Exception::class);
        $countries['FOO'];
    }

    public function testArrayAccessWithInvalidCountryCode(): void
    {
        $countries = new Countries();
        $this->expectException(Exception::class);
        $countries['FOO'];
    }

    public function testArrayAccessSetValue(): void
    {
        $countries = new Countries();
        $this->expectException(Exception::class);
        $countries['FOO'] = 'bar';
    }

    public function testArrayAccessUnsetValue(): void
    {
        $countries = new Countries();
        $this->expectException(Exception::class);
        unset($countries['FOO']);
    }

    public function testHasCode(): void
    {
        $countries = new Countries();
        $this->assertFalse($countries->hasCountryCode('FOO'));
        $this->assertTrue($countries->hasCountryCode('NL'));
    }

    public function testIsCodeInEU(): void
    {
        $countries = new Countries();
        $this->assertFalse($countries->isCountryCodeInEU('FOO'));
        $this->assertFalse($countries->isCountryCodeInEU('US'));
        $this->assertTrue($countries->isCountryCodeInEU('NL'));

        // test for Brexit
        $now = new DateTime('now');
        $y2021 = new DateTime('2020-12-31 23:59:59');
        if ($now > $y2021) {
            $this->assertFalse($countries->isCountryCodeInEU('GB'));
        } else {
            $this->assertTrue($countries->isCountryCodeInEU('GB'));
        }
    }
}
