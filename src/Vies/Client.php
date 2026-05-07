<?php

declare(strict_types=1);

namespace Ibericode\Vat\Vies;

use SoapClient;
use SoapFault;

class Client
{
    /**
     * @const string
     */
    private const URL = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    /**
     * @var SoapClient
     */
    private $client;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * Client constructor.
     *
     * @param int $timeout How long should we wait before aborting the request to VIES?
     */
    public function __construct(int $timeout = 10)
    {
        $this->timeout = $timeout;
    }

    /**
     * @param string $countryCode
     * @param string $vatNumber
     *
     * @return bool
     *
     * @throws ViesException
     */
    public function checkVat(string $countryCode, string $vatNumber): bool
    {
        $info = $this->getInfo($countryCode, $vatNumber);

        if (!isset($info->valid)) {
            throw new ViesException('VIES response is missing the "valid" field.');
        }

        return (bool) $info->valid;
    }

    /**
     * @param string $countryCode
     * @param string $vatNumber
     *
     * @return object
     *
     * @throws ViesException
     */
    public function getInfo(string $countryCode, string $vatNumber): object
    {
        try {
            $response = $this->getClient()->checkVat(
                array(
                    'countryCode' => $countryCode,
                    'vatNumber' => $vatNumber
                )
            );
        } catch (SoapFault $e) {
            if (ViesServiceUnavailableException::isTransientFault($e->getMessage())) {
                throw new ViesServiceUnavailableException($e->getMessage(), (int) $e->getCode(), $e);
            }

            throw new ViesException($e->getMessage(), (int) $e->getCode(), $e);
        }

        return $response;
    }

    /**
     * @return SoapClient
     */
    protected function getClient(): SoapClient
    {
        if ($this->client === null) {
            $this->client = new SoapClient(self::URL, [
                'connection_timeout' => $this->timeout,
                'cache_wsdl' => WSDL_CACHE_DISK,
                'keep_alive' => false,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                'stream_context' => stream_context_create([
                    'http' => [
                        'timeout' => $this->timeout,
                    ],
                ]),
            ]);
        }

        return $this->client;
    }
}
