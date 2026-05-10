<?php

namespace Ibericode\Vat\Tests\Vies;

use Ibericode\Vat\Vies\Client;
use Ibericode\Vat\Vies\ViesException;
use Ibericode\Vat\Vies\ViesServiceUnavailableException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class ClientTest extends TestCase
{
    public function testCheckVatReturnsTrueWhenValid(): void
    {
        $client = $this->makeClientReturning((object) ['valid' => true]);
        $this->assertTrue($client->checkVat('NL', '123456789B01'));
    }

    public function testCheckVatReturnsFalseWhenInvalid(): void
    {
        $client = $this->makeClientReturning((object) ['valid' => false]);
        $this->assertFalse($client->checkVat('NL', '000000000B00'));
    }

    public function testCheckVatThrowsWhenValidFieldMissing(): void
    {
        $client = $this->makeClientReturning(new stdClass());
        $this->expectException(ViesException::class);
        $client->checkVat('NL', '123456789B01');
    }

    #[DataProvider('transientFaultStrings')]
    public function testTransientFaultStringsAreClassified(string $faultString): void
    {
        $this->assertTrue(ViesServiceUnavailableException::isTransientFault($faultString));
    }

    public static function transientFaultStrings(): array
    {
        return [
            ['MS_UNAVAILABLE'],
            ['SERVICE_UNAVAILABLE'],
            ['TIMEOUT'],
            ['MS_MAX_CONCURRENT_REQ'],
            ['GLOBAL_MAX_CONCURRENT_REQ'],
            ['IP_BLOCKED'],
            ['{ MS_UNAVAILABLE } [Server]'],
            ['ms_unavailable'],
        ];
    }

    #[DataProvider('definitiveFaultStrings')]
    public function testDefinitiveFaultStringsAreNotTransient(string $faultString): void
    {
        $this->assertFalse(ViesServiceUnavailableException::isTransientFault($faultString));
    }

    public static function definitiveFaultStrings(): array
    {
        return [
            ['INVALID_INPUT'],
            ['INVALID_REQUESTER_INFO'],
            [''],
            ['random unrelated message'],
        ];
    }

    public function testServiceUnavailableExceptionExtendsViesException(): void
    {
        $this->assertTrue(is_subclass_of(ViesServiceUnavailableException::class, ViesException::class));
    }

    private function makeClientReturning(object $info): Client
    {
        return new class ($info) extends Client {
            public function __construct(private object $info)
            {
                parent::__construct();
            }

            public function getInfo(string $countryCode, string $vatNumber): object
            {
                return $this->info;
            }
        };
    }
}
