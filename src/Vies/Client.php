<?php

namespace DvK\Vat\Vies;

use SoapClient;
use SoapFault;

class Client {

    /**
     * @const string
     */
    const URL = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

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
    public function __construct($timeout = 10) {
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
    public function checkVat( $countryCode, $vatNumber ) {
        try {
            $response = $this->getClient()->checkVat(
                array(
                    'countryCode' => $countryCode,
                    'vatNumber' => $vatNumber
                )
            );
        } catch( SoapFault $e ) {
            throw new ViesException( $e->getMessage(), $e->getCode() );
        }

        return (bool) $response->valid;
    }

    /**
     * @return SoapClient
     */
    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = new SoapClient(self::URL, ['connection_timeout' => $this->timeout]);
        }

        return $this->client;
    }
}
