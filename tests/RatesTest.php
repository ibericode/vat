<?php

namespace Ibericode\Vat\Tests;

use Ibericode\Vat\Clients\ClientException;
use Ibericode\Vat\Clients\JsonVatClient;
use Ibericode\Vat\Exception;
use Ibericode\Vat\Period;
use Ibericode\Vat\Rates;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;

class RatesTest extends TestCase
{
    public function setUp() : void
    {
        if (file_exists('vendor/rates')) {
            unlink('vendor/rates');
        }
    }

    private function getJsonVatMock()
    {
        $client = $this->getMockBuilder(JsonVatClient::class)->getMock();
        $client
            ->method('fetch')
            ->willReturn([
                'NL' => [
                    new Period(new \DateTime('2000/01/01'), [
                        'standard' => 19.00,
                        'reduced' => 6.00,
                    ]),
                    new Period(new \DateTime('2012/01/01'), [
                        'standard' => 21.00,
                        'reduced' => 6.00,
                    ]),
                    new Period(new \DateTime('2019/01/01'), [
                        'standard' => 21.00,
                        'reduced' => 9.00,
                    ])
                ]
            ]);

        $client
            ->expects($this->once())
            ->method('fetch');

        return $client;
    }

    public function testGetRateForCountry()
    {
        $client = $this->getJsonVatMock();
        $rates = new Rates('vendor/rates', 30, $client);
        $this->assertEquals(21.0,  $rates->getRateForCountry('NL'));
    }

    public function testGetRateForCountryOnDate()
    {
        $client = $this->getJsonVatMock();
        $rates = new Rates('vendor/rates', 30, $client);
        $this->assertEquals(19.0,  $rates->getRateForCountryOnDate('NL', new \DateTime('2011/01/01')));
        $this->assertEquals(6.0,  $rates->getRateForCountryOnDate('NL', new \DateTime('2018/01/01'), 'reduced'));

        $this->assertEquals(21.0,  $rates->getRateForCountryOnDate('NL', new \DateTime('2019/01/01')));
        $this->assertEquals(9.0,  $rates->getRateForCountryOnDate('NL', new \DateTime('2019/01/01'), 'reduced'));
    }

    public function testGetRateForCountryWithInvalidCountryCode()
    {
        $client = $this->getJsonVatMock();
        $rates = new Rates('vendor/rates', 30, $client);
        $this->expectException(Exception::class);
        $rates->getRateForCountry('FOO');
    }

    public function testRatesAreLoadedFromFile()
    {
        $client = $this->getJsonVatMock();
        $rates = new Rates('vendor/rates', 30, $client);
        $this->assertEquals(21.0,  $rates->getRateForCountry('NL'));

        // test by ensuring client::fetch is never called
        $rates = new Rates('vendor/rates', 30, $client);
        $client->expects($this->never())->method('fetch');
        $this->assertEquals(21.0,  $rates->getRateForCountry('NL'));

        // test by invalidating file and testing for exception
        file_put_contents('vendor/rates', 'foobar');
        $rates = new Rates('vendor/rates', 30, $client);
        $this->expectException(Error::class);
        $this->assertEquals(21.0,  $rates->getRateForCountry('NL'));
    }

    public function testRatesAreLoadedFromFileOnClientException()
    {
        // first, populate local file
        $client = $this->getJsonVatMock();
        $rates = new Rates('vendor/rates', 10, $client);
        $this->assertEquals(21.0,  $rates->getRateForCountry('NL'));

        // then, perform test
        $client = $this->getJsonVatMock();
        $client->method('fetch')->willThrowException(new ClientException('Service is down'));
        $rates = new Rates('vendor/rates', -1, $client);
        $this->assertEquals(21.0,  $rates->getRateForCountry('NL'));
    }

}