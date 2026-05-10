<?php

namespace Ibericode\Vat\Tests;

use Ibericode\Vat\Clients\ClientException;
use Ibericode\Vat\Clients\IbericodeVatRatesClient;
use Ibericode\Vat\Exception;
use Ibericode\Vat\Period;
use Ibericode\Vat\Rates;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\TestCase;

class RatesTest extends TestCase
{
    protected function setUp(): void
    {
        if (file_exists('vendor/rates')) {
            unlink('vendor/rates');
        }
    }

    private function getRatesClientMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        $client = $this->getMockBuilder(IbericodeVatRatesClient::class)
            ->getMock();
        $client
            ->expects($this->once())
            ->method('fetch')
            ->willReturn([
                'NL' => [
                    new Period(new \DateTimeImmutable('2000/01/01'), [
                        'standard' => 19.00,
                        'reduced' => 6.00,
                    ]),
                    new Period(new \DateTimeImmutable('2012/01/01'), [
                        'standard' => 21.00,
                        'reduced' => 6.00,
                    ]),
                    new Period(new \DateTimeImmutable('2019/01/01'), [
                        'standard' => 21.00,
                        'reduced' => 9.00,
                    ])
                ]
            ]);

        return $client;
    }

    public function testGetRateForCountry()
    {
        $client = $this->getRatesClientMock();
        $rates = new Rates('vendor/rates', 30, $client);
        $this->assertEquals(21.0, $rates->getRateForCountry('NL'));
    }

    public function testGetRateForCountryOnDate()
    {
        $client = $this->getRatesClientMock();
        $rates = new Rates('vendor/rates', 30, $client);
        $this->assertEquals(19.0, $rates->getRateForCountryOnDate('NL', new \DateTime('2011/01/01')));
        $this->assertEquals(6.0, $rates->getRateForCountryOnDate('NL', new \DateTime('2018/01/01'), 'reduced'));

        $this->assertEquals(21.0, $rates->getRateForCountryOnDate('NL', new \DateTime('2019/01/01')));
        $this->assertEquals(9.0, $rates->getRateForCountryOnDate('NL', new \DateTime('2019/01/01'), 'reduced'));
    }

    public function testGetRateForCountryWithInvalidCountryCode()
    {
        $client = $this->getRatesClientMock();
        $rates = new Rates('vendor/rates', 30, $client);
        $this->expectException(Exception::class);
        $rates->getRateForCountry('FOO');
    }

    public function testRatesAreLoadedFromFile()
    {
        $client = $this->getRatesClientMock();
        $rates = new Rates('vendor/rates', 30, $client);
        $this->assertEquals(21.0, $rates->getRateForCountry('NL'));

        // test by ensuring client::fetch is never called
        $rates = new Rates('vendor/rates', 30, $client);
        $client->expects($this->never())->method('fetch');
        $this->assertEquals(21.0, $rates->getRateForCountry('NL'));

        // test by invalidating file and testing for exception
        file_put_contents('vendor/rates', 'foobar');
        $rates = new Rates('vendor/rates', 30, $client);
        $this->expectException(Exception::class);
        $this->assertEquals(21.0, $rates->getRateForCountry('NL'));
    }

    public function testRatesAreLoadedFromFileOnClientException()
    {
        // first, populate local file
        $client = $this->getRatesClientMock();
        $rates = new Rates('vendor/rates', 10, $client);
        $this->assertEquals(21.0, $rates->getRateForCountry('NL'));

        // then, perform test
        $client = $this->getRatesClientMock();
        $client->method('fetch')->willThrowException(new ClientException('Service is down'));
        $rates = new Rates('vendor/rates', -1, $client);
        $this->assertEquals(21.0, $rates->getRateForCountry('NL'));
    }

    public function testPeriodsAreSortedDescendingAndStable(): void
    {
        $client = $this->getMockBuilder(IbericodeVatRatesClient::class)->getMock();
        $client->method('fetch')->willReturn([
            'NL' => [
                new Period(new \DateTimeImmutable('2010-01-01'), ['standard' => 19.0]),
                new Period(new \DateTimeImmutable('2020-01-01'), ['standard' => 21.0]),
                new Period(new \DateTimeImmutable('2015-01-01'), ['standard' => 21.0]),
            ],
        ]);

        $rates = new Rates('vendor/rates', 30, $client);
        $this->assertEquals(21.0, $rates->getRateForCountryOnDate('NL', new \DateTime('2021-01-01')));
        $this->assertEquals(21.0, $rates->getRateForCountryOnDate('NL', new \DateTime('2016-01-01')));
        $this->assertEquals(19.0, $rates->getRateForCountryOnDate('NL', new \DateTime('2011-01-01')));
    }

    public function testCacheIsJsonFormatted(): void
    {
        $path = 'vendor/rates';
        $client = $this->getRatesClientMock();
        $rates = new Rates($path, 30, $client);
        $rates->getRateForCountry('NL');

        $contents = file_get_contents($path);
        $this->assertNotFalse($contents);
        $decoded = json_decode($contents, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('NL', $decoded);
        $this->assertArrayHasKey('effective_from', $decoded['NL'][0]);
        $this->assertArrayHasKey('rates', $decoded['NL'][0]);
    }

    public function testCacheRoundTripsThroughJson(): void
    {
        $path = 'vendor/rates';

        // First instance: writes the cache from the mock fetch.
        $client = $this->getRatesClientMock();
        $rates = new Rates($path, 3600, $client);
        $this->assertEquals(21.0, $rates->getRateForCountry('NL'));

        // Second instance with a never-fetched mock: reads back from the file.
        $unusedClient = $this->getMockBuilder(IbericodeVatRatesClient::class)->getMock();
        $unusedClient->expects($this->never())->method('fetch');
        $rates = new Rates($path, 3600, $unusedClient);
        $this->assertEquals(21.0, $rates->getRateForCountry('NL'));
        $this->assertEquals(9.0, $rates->getRateForCountryOnDate('NL', new \DateTime('2020-01-01'), 'reduced'));
    }

    public function testCacheWriteIsAtomic()
    {
        $path = 'vendor/rates';
        $client = $this->getRatesClientMock();
        $rates = new Rates($path, 30, $client);
        $rates->getRateForCountry('NL');

        $this->assertFileExists($path);

        $leftover = glob($path . '.*.tmp') ?: [];
        $this->assertSame([], $leftover, 'No .tmp files should remain after a successful write.');
    }
}
