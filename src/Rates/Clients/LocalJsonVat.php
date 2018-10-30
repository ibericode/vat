<?php

namespace DvK\Vat\Rates\Clients;

use DvK\Vat\Rates\Exceptions\ClientException;
use DvK\Vat\Rates\Interfaces\Client;

/**
 * Returns the data from jsonvat.com from local file that is included in this repository.
 *
 * This makes this library independent of jsonvat.com and possible failures.
 */
final class LocalJsonVat implements Client
{
    public function fetch()
    {
        $json = file_get_contents(__DIR__ . '/../../../data/jsonvat_rates.json');
        $data = json_decode($json, true);
        $output = array_combine(array_column($data['rates'], 'country_code'), $data['rates']);

        return $output;
    }
}
