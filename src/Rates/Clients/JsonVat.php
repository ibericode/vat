<?php

namespace DvK\Vat\Rates\Clients;

use DvK\Vat\Rates\Exceptions\ClientException;
use DvK\Vat\Rates\Interfaces\Client;

class JsonVat implements Client{

    /**
     * @const string
     */
    const URL = 'https://jsonvat.com/';

    /**
     * @throws ClientException
     *
     * @return array
     */
    public function fetch() {
        $url = self::URL;

        // fetch data
        $response = file_get_contents($url);
        if( empty( $response ) ) {
            throw new ClientException( "Error fetching rates from {$url}.");
        }
        $data = json_decode($response);

        // build map with country codes => rates
        $map = array();
        foreach ($data->rates as $rate) {
            $map[$rate->country_code] = $rate->periods[0]->rates;
        }

        return $map;
    }
}
