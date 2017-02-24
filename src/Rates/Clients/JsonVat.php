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

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        $response_body = curl_exec($curl_handle);
        curl_close($curl_handle);

        if( empty( $response_body ) ) {
            throw new ClientException( "Error fetching rates from {$url}.");
        }

        $data = json_decode($response_body, true);
        $output = array_combine(array_column($data['rates'], 'country_code'), $data['rates']);
        return $output;
    }
}
