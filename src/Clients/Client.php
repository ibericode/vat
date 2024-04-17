<?php

namespace Ibericode\Vat\Clients;

interface Client
{
    /**
     * This method should return an associative array in the following format:
     *
     * [
     *  'NL' => [
     *      new Period(DateTime $effectiveFrom, array $rates)
     *    ]
     * ]
     *
     * @see https://github.com/ibericode/vat-rates*
     * @return array
     * @throws ClientException
     */
    public function fetch(): array;
}
