<?php

namespace Ibericode\Vat\Interfaces;

use Ibericode\Vat\Exceptions\ClientException;

interface Client {

    /**
     * This method should return an associative array in the following format:
     *
     * [
     *    'NL' => [
     *        'name'         => 'Netherlands',
     *        'code'         => 'NL',
     *        'country_code' => 'NL',
     *        'periods'      => [
     *            [
     *            'effective_from' => '2012-10-01',
     *            'rates'          => [
     *                'reduced'  => 6.0,
     *                'standard' => 21.0,
     *                ],
     *            ],
     *            [
     *            'effective_from' => '0000-01-01',
     *            'rates'          => [
     *                'reduced'  => 5.0,
     *                'standard' => 19.0,
     *                ],
     *            ],
     *        ],
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
