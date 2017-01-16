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
    protected $client;

    /**
     * Client constructor.
     */
    public function __construct() {
        $this->client = new SoapClient( self::URL, [ 'connection_timeout' => 10 ]);
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
            $response = $this->client->checkVat(
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
}
