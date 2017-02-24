<?php

namespace DvK\Vat\Rates\Caches;

use Psr\SimpleCache\CacheInterface;

class NullCache implements CacheInterface
{
    /**
     * @param string $key
     * @param (optional) mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param null|int|\DateInterval $ttl
     *
     * @return bool
     */
    public function set($key, $value, $expiration = null)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function delete($key)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        return true;
    }

    /**
     * @param iterable $keys
     * @param mixed $default
     *
     * @return iterable
     */
    public function getMultiple($keys, $default = null)
    {
        return $default;
    }

    /**
     * @param iterable $values
     * @param null|int|\DateInterval $ttl
     *
     * @return bool
     */
    public function setMultiple($values, $ttl = null)
    {
        return true;
    }

    /**
     * @param iterable $keys
     *
     * @return bool
     */
    public function deleteMultiple($keys)
    {
        return true;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return false;
    }
}
