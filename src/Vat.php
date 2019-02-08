<?php
declare(strict_types=1);

namespace Ibericode\Vat;

use Ibericode\Vat\Cache\NullCache;
use Ibericode\Vat\Clients\JsonVat;
use Ibericode\Vat\Interfaces\Client;
use Psr\SimpleCache\CacheInterface;

class Vat {
    private $cache;
    private $client;
    private $rates = [];
    private $options;

    public function __construct(CacheInterface $cache = null, Client $client = null, array $options = [])
    {
        $this->cache = $cache;
        $this->client = $client;
        $this->options = array_merge([
            'ttl' => 7200, // 2 hours
        ], $options);
    }

    private function fetchRates()
    {
        if (count($this->rates) > 0) {
            return;
        }

        $this->cache = $this->cache ?: new NullCache();
        if ($this->cache->has('ibericode-vat-rates')) {
            $this->rates = $this->cache->get('ibericode-vat-rates');
            return;
        }

        $this->client = $this->client ?: new JsonVat();
        $this->rates = $this->client->fetch();
        $this->cache->set('ibericode-vat-rates', $this->rates, $this->options['ttl']);
    }

    public function getCountry(string $countryCode) : Country
    {
        $this->fetchRates();
        $countries = new Countries();
        $rates = isset($this->rates[$countryCode]) ? $this->rates[$countryCode] : [];
        return $countries->get($countryCode, $rates);
    }

}