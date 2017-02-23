<?php

namespace DvK\Tests\Vat;

use DvK\Vat\Rates\Caches\NullCache;
use DvK\Vat\Rates\Exceptions\Exception;
use DvK\Vat\Rates\Rates;
use DvK\Vat\Rates\Clients\JsonVat;

use PHPUnit_Framework_TestCase;

/**
 * Class RatesTest
 *
 * @package DvK\Tests\Vat
 */
class RatesTest extends PHPUnit_Framework_TestCase
{
    /**
     * Mock JsonVat client so remote API is not hit
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getClientMock() {
        $mock = self::getMockBuilder(JsonVat::class)
            ->getMock();
       return $mock;
    }

    /**
     * Mock Cache clientso we can test whether put and get methods are being called without depending on a Cache class.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getCacheMock() {
        $mock = self::getMockBuilder(NullCache::class)
            ->getMock();

        return $mock;
    }

    /**
     * @throws Exception
     *
     * @covers Rates::country
     */
    public function test_country() {
        $data = [
            'NL' => [
                'name'         => 'Netherlands',
                'code'         => 'NL',
                'country_code' => 'NL',
                'periods'      =>
                    [
                        [
                            'effective_from' => '2020-01-01',
                            'rates'          =>
                                [
                                    'reduced'  => 7.0,
                                    'standard' => 22.0,
                                ],
                        ],
                        [
                            'effective_from' => '2012-10-01',
                            'rates'          =>
                                [
                                    'reduced'  => 6.0,
                                    'standard' => 21.0,
                                ],
                        ],
                        [
                            'effective_from' => '0000-01-01',
                            'rates'          =>
                                [
                                    'reduced'  => 5.0,
                                    'standard' => 19.0,
                                ],
                        ],
                    ],
            ]
        ];
        $mock = $this->getClientMock();
        $mock
            ->method('fetch')
            ->will(self::returnValue( $data ));

        $rates = new Rates($mock, null);

        // Return correct VAT rates
        self::assertEquals(  $rates->country('NL'), 21 );
        self::assertEquals(  $rates->country('NL', 'reduced'), 6 );

        // Return correct VAT rates on an older period
        self::assertEquals($rates->country('NL', 'standard', new \DateTimeImmutable('2010-01-01')), 19);
        self::assertEquals($rates->country('NL', 'reduced', new \DateTimeImmutable('2010-01-01')), 5);

        // Return correct VAT rates on an future period
        self::assertEquals($rates->country('NL', 'standard', new \DateTimeImmutable('2022-01-01')), 22);
        self::assertEquals($rates->country('NL', 'reduced', new \DateTimeImmutable('2022-01-01')), 7);

        // Exception when supplying country code for which we have no rate
        self::expectException( 'Exception' );
        $rates->country('US');
    }

    /**
     * @covers Rates::all()
     */
    public function test_all() {
        $data = [
            'NL' => [
                'name'         => 'Netherlands',
                'code'         => 'NL',
                'country_code' => 'NL',
                'periods'      =>
                    [
                        [
                            'effective_from' => '2020-01-01',
                            'rates'          =>
                                [
                                    'reduced'  => 7.0,
                                    'standard' => 22.0,
                                ],
                        ],
                        [
                            'effective_from' => '2012-10-01',
                            'rates'          =>
                                [
                                    'reduced'  => 6.0,
                                    'standard' => 21.0,
                                ],
                        ],
                        [
                            'effective_from' => '0000-01-01',
                            'rates'          =>
                                [
                                    'reduced'  => 5.0,
                                    'standard' => 19.0,
                                ],
                        ],
                    ],
            ]
        ];
        $mock = $this->getClientMock();
        $mock
            ->method('fetch')
            ->will(self::returnValue( $data ));

        $rates = new Rates($mock, null);
        self::assertEquals( $data, $rates->all());
    }

    /**
     * @covers Rates::load
     */
    public function test_ratesAreLoadedFromCache() {
        $mock = $this->getCacheMock();
        $data = [
            'NL' => [
                'name'         => 'Netherlands',
                'code'         => 'NL',
                'country_code' => 'NL',
                'periods'      =>
                    [
                        [
                            'effective_from' => '2020-01-01',
                            'rates'          =>
                                [
                                    'reduced'  => 7.0,
                                    'standard' => 22.0,
                                ],
                        ],
                        [
                            'effective_from' => '2012-10-01',
                            'rates'          =>
                                [
                                    'reduced'  => 6.0,
                                    'standard' => 21.0,
                                ],
                        ],
                        [
                            'effective_from' => '0000-01-01',
                            'rates'          =>
                                [
                                    'reduced'  => 5.0,
                                    'standard' => 19.0,
                                ],
                        ],
                    ],
            ]
        ];

        $mock
            ->method('get')
            ->with('vat-rates')
            ->will(self::returnValue($data));

        $rates = new Rates( null, $mock );

        self::assertNotEmpty($rates->all());
        self::assertEquals($rates->all(), $data);

        // Return correct VAT rates
        self::assertEquals($rates->country('NL'), 21);
        self::assertEquals($rates->country('NL', 'reduced'), 6);

        // Return correct VAT rates on an older period
        self::assertEquals($rates->country('NL', 'standard', new \DateTimeImmutable('2010-01-01')), 19);
        self::assertEquals($rates->country('NL', 'reduced', new \DateTimeImmutable('2010-01-01')), 5);

        // Return correct VAT rates on an future period
        self::assertEquals($rates->country('NL', 'standard', new \DateTimeImmutable('2022-01-01')), 22);
        self::assertEquals($rates->country('NL', 'reduced', new \DateTimeImmutable('2022-01-01')), 7);
    }

    /**
     *  @covers Rates::load
     */
    public function test_ratesAreStoredInCache() {
        $cacheMock = $this->getCacheMock();
        $clientMock = $this->getClientMock();

        $cacheMock
            ->expects(self::once())
            ->method('set');

        $rates = new Rates( $clientMock, $cacheMock );
    }


}
