<?php

namespace Ibericode\Vat\Tests\Vies;

use Ibericode\Vat\Vies\Client;
use Ibericode\Vat\Vies\ViesException;
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
