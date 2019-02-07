<?php

namespace Ibericode\Vat;

use DvK\Vat\Rates\Clients\JsonVat;
use Psr\SimpleCache\CacheInterface;

class Vat {
    private $cache;
    private $rates;
    private $countries;

    public function __construct(CacheInterface $cache = null)
    {
        $this->cache = $cache;
        $this->countries = new Countries();
    }

    private function fetchRates()
    {
        if (count($this->rates) > 0) {
            return;
        }

        if ($this->cache instanceof CacheInterface && $this->cache->has('ibericode-vat-rates')) {
            $this->rates = $this->cache->get('ibericode-vat-rates');
            return;
        }

        $client = new JsonVat();
        $this->rates = $client->fetch();

        if ($this->cache instanceof CacheInterface) {
            $this->cache->set('ibericode-vat-rates', $this->rates);
        }
    }

    public function getCountry(string $countryCode) : Country
    {
        $this->fetchRates();
        return $this->countries->get($countryCode, $this->rates[$countryCode] ?: []);
    }

}