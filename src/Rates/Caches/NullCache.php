<?php

namespace DvK\Vat\Rates\Caches;

use DvK\Vat\Rates\Interfaces\Cache;

class NullCache implements Cache {

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     *
     * @return mixed
     */
    public function put($key, $value, $expiration)
    {
        return null;
    }

}