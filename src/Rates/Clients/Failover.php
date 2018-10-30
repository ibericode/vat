<?php

namespace DvK\Vat\Rates\Clients;

use DvK\Vat\Rates\Exceptions\ClientException;
use DvK\Vat\Rates\Interfaces\Client;

/**
 * Try to fetch the vat rates using the primary client, but fall back to the failover if the primary fails.
 */
final class Failover implements Client
{
    /**
     * @var Client
     */
    private $primary;

    /**
     * @var Client
     */
    private $failover;

    public function __construct(Client $primary, Client $failover)
    {
        $this->primary = $primary;
        $this->failover = $failover;
    }

    public function fetch()
    {
        try {
            return $this->primary->fetch();
        } catch (ClientException $e) {
            return $this->failover->fetch();
        }
    }
}
