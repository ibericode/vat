<?php
declare(strict_types=1);

namespace Ibericode\Vat\Clients;

use Ibericode\Vat\Exceptions\ClientException;
use Ibericode\Vat\Interfaces\Client;
use Ibericode\Vat\Period;

class JsonVat implements Client{

    /**
     * @throws ClientException
     *
     * @return array
     */
    public function fetch() : array 
    {
        $url = 'https://jsonvat.com/';

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        $response_body = curl_exec($curl_handle);
        curl_close($curl_handle);

        if( empty($response_body) ) {
            throw new ClientException( "Error fetching rates from {$url}.");
        }

        return $this->parseResponse($response_body);

    }

    private function parseResponse(string $response_body) : array
    {
        $data = json_decode($response_body, false);

        $return = [];
        foreach ($data->rates as $country_rates) {
            $periods = [];

            foreach ($country_rates->periods as $period) {
                $periods[] = new Period(new \DateTimeImmutable($period->effective_from), (array) $period->rates);
            }

            $return[$country_rates->country_code] = $periods;
        }

        return $return;
    }
}
