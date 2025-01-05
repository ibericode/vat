<?php

namespace Ibericode\Vat\Tests\Clients;

use Ibericode\Vat\Clients\ClientException;
use Ibericode\Vat\Clients\IbericodeVatRatesClient;
use Ibericode\Vat\Clients\Client;
use Ibericode\Vat\Period;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClientsTest extends TestCase
{
    /**
     * @group remote-http
     * @throws ClientException
     */
    #[DataProvider('clientsProvider')]
    public function testClient(Client $client): void
    {
        $data = $client->fetch();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('NL', $data);
        $this->assertIsArray($data['NL']);
        $this->assertInstanceOf(Period::class, $data['NL'][0]);
    }

    public static function clientsProvider(): \Generator
    {
        yield [new IbericodeVatRatesClient()];
    }
}
