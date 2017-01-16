<?php

namespace DvK\Vat\Rates\Interfaces;

use DvK\Vat\Rates\Exceptions\ClientException;

/**
 * Interface Client
 *
 * @package DvK\Vat\Rates\Interfaces
 */
interface Client {

    /**
     * This methods should return an associative array in the following format:
     *
     * [
     *    'DE' => [
     *      'standard' => 19,
     *      'reduced' => 7.0,
     *    ],
     *    ...
     * ]
     *
     * @throws ClientException
     *
     * @return array
     */
    public function fetch();
}
