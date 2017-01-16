<?php

namespace DvK\Vat\Rates\Interfaces;

/**
 * Interface Cache
 *
 * @package DvK\Vat\Rates\Interfaces
 */
interface Cache {

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get( $key );

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     *
     * @return mixed
     */
    public function put( $key, $value, $expiration );
}
