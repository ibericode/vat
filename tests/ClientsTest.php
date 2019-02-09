<?php

namespace Ibericode\Vat\Tests\Clients;

use Ibericode\Vat\Clients\IbericodeVatRates;
use Ibericode\Vat\Clients\JsonVat;
use Ibericode\Vat\Interfaces\Client;
use Ibericode\Vat\Period;
use PHPUnit\Framework\TestCase;

class ClientsTest extends TestCase {

    /**
     * @group remote-http
     * @dataProvider clientProvider
     */
    public function testClient(Client $client)
    {
        $data = $client->fetch();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('NL', $data);
        $this->assertIsArray($data['NL']);
        $this->assertInstanceOf(Period::class, $data['NL'][0]);
    }

    public function clientProvider()
    {
        yield [new JsonVat()];
        yield [new IbericodeVatRates()];
    }
}