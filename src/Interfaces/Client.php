<?php

namespace Ibericode\Vat\Interfaces;

use Ibericode\Vat\Exceptions\ClientException;

interface Client {

    /**
     * This method should return an associative array in the following format:
     *
     * [
     *  'NL' => [
     *      new Period(DateTime $effectiveFrom, array $rates)
     *    ]
     * ]
     *
     *
     * @throws ClientException
     *
     * @return array
     */
    public function fetch() : array;
}
