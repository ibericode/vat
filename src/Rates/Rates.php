<?php

namespace DvK\Vat\Rates;

use DvK\Vat\Rates\Caches\NullCache;
use DvK\Vat\Rates\Clients\JsonVat;
use DvK\Vat\Rates\Interfaces\Client;
use DvK\Vat\Rates\Exceptions\Exception;
use Psr\SimpleCache\CacheInterface;

class Rates
{

    /**
     * @var array
     */
    protected $map = array();

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Rates constructor.
     *
     * @param Client $client     (optional)
     * @param CacheInterface $cache          (optional)
     */
    public function __construct( Client $client = null, CacheInterface $cache = null )
    {
        $this->client = $client;
        $this->cache = $cache ? $cache : new NullCache();
        $this->map = $this->load();
    }

    protected function load()
    {
        // load from cache
        $map = $this->cache->get('vat-rates');

        // fetch from jsonvat.com
        if (empty($map)) {
            $map = $this->fetch();

            // store in cache
            $this->cache->set('vat-rates', $map, 86400);
        }

        return $map;
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    protected function fetch()
    {
        if( ! $this->client ) {
            $this->client = new JsonVat();
        }

        return $this->client->fetch();
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->map;
    }

    /**
     * @param string $country
     * @param string $rate
     * @param \DateTimeInterface $applicableDate - optionnal - the applicable date
     *
     * @return double
     *
     * @throws Exception
     */
    public function country($country, $rate = 'standard', \DateTimeInterface $applicableDate = null)
    {
        $country = strtoupper($country);
        $country = $this->getCountryCode($country);

        if (null === $applicableDate) {
            $applicableDate = new \DateTime('today midnight');
        }

        if (!isset($this->map[$country])) {
            throw new Exception('Invalid country code.');
        }

        $periods = $this->map[$country]['periods'];

        // Sort by date desc
        usort($periods, function ($period1, $period2) {
            return new \DateTime($period1['effective_from']) > new \DateTime($period2['effective_from']) ? -1 : 1;
        });

        foreach ($periods AS $period) {
            if (new \DateTime($period['effective_from']) > $applicableDate) {
                continue;
            }
            else {
                break;
            }
        }

        if (empty($period)) {
            throw new Exception('Unable to find a rate applicable at that date.');
        }

        if (!isset($period['rates'][$rate])) {
            throw new Exception('Invalid rate.');
        }

        return $period['rates'][$rate];
    }

    /**
     * Get normalized country code
     *
     * Fixes ISO-3166-1-alpha2 exceptions
     *
     * @param string $country
     * @return string
     */
    protected function getCountryCode($country)
    {
        if ($country == 'UK') {
            $country = 'GB';
        }

        if ($country == 'EL') {
            $country = 'GR';
        }

        return $country;
    }


}
