<?php

namespace Ibericode\Vat\Tests\Clients;

use Ibericode\Vat\Clients\ClientException;
use Ibericode\Vat\Clients\IbericodeVatRatesClient;
use Ibericode\Vat\Clients\Client;
use Ibericode\Vat\Period;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

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

    #[DataProvider('malformedResponseProvider')]
    public function testParseResponseThrowsOnMalformedBody(string $body): void
    {
        $client = new IbericodeVatRatesClient();
        $method = new ReflectionMethod($client, 'parseResponse');
        $this->expectException(ClientException::class);
        $method->invoke($client, $body);
    }

    public static function malformedResponseProvider(): \Generator
    {
        yield 'empty body' => [''];
        yield 'invalid JSON' => ['not json'];
        yield 'JSON null' => ['null'];
        yield 'JSON without items' => ['{"foo":"bar"}'];
        yield 'items not an object' => ['{"items":"oops"}'];
        yield 'period entry missing fields' => ['{"items":{"NL":[{"foo":"bar"}]}}'];
        yield 'periods not an array' => ['{"items":{"NL":"oops"}}'];
    }

    public function testParseResponseAcceptsValidBody(): void
    {
        $body = '{"items":{"NL":[{"effective_from":"2019-01-01","rates":{"standard":21.0,"reduced":9.0}}]}}';
        $client = new IbericodeVatRatesClient();
        $method = new ReflectionMethod($client, 'parseResponse');
        $data = $method->invoke($client, $body);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('NL', $data);
        $this->assertInstanceOf(Period::class, $data['NL'][0]);
    }
}
