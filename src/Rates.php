<?php
declare(strict_types=1);

namespace Ibericode\Vat;

use DateTimeInterface;
use Ibericode\Vat\Cache\NullCache;
use Ibericode\Vat\Clients\IbericodeVatRates;
use Ibericode\Vat\Exceptions\Exception;
use Ibericode\Vat\Interfaces\Client;
use Psr\SimpleCache\CacheInterface;

class Rates {
    const RATE_STANDARD = 'standard';
    const RATE_REDUCED = 'reduced';

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

    private function load()
    {
        if (count($this->rates) > 0) {
            return;
        }

        $this->cache = $this->cache ?: new NullCache();
        if ($this->cache->has('ibericode-vat-rates')) {
            $this->rates = $this->cache->get('ibericode-vat-rates');
            return;
        }

        $this->client = $this->client ?: new IbericodeVatRates();
        $this->rates = $this->client->fetch();

        // Sort periods by DateTime (DESC)
        foreach ($this->rates as $country => $periods) {
            usort($this->rates[$country], function (Period $period1, Period $period2) {
                return $period1->getEffectiveFrom() > $period2->getEffectiveFrom() ? -1 : 1;
            });
        }

        $this->cache->set('ibericode-vat-rates', $this->rates, $this->options['ttl']);
    }

    private function resolvePeriod(string $countryCode, DateTimeInterface $datetime) : Period
    {
        $this->load();

        if (!isset($this->rates[$countryCode])) {
            throw new Exception("Invalid country code {$countryCode}");
        }

        // find first active period (because periods are sorted)
        foreach ($this->rates[$countryCode] as $period) {
            /** @var Period $period */
            if ($datetime >= $period->getEffectiveFrom()) {
                return $period;
            }
        }

        throw new Exception("Unable to find a rate for country {$countryCode} on {$datetime->format(DATE_ATOM)}.");
    }

    /**
     * @param string $countryCode ISO-3166-1-alpha2 country code
     * @param string $level
     * @return float
     * @throws \Exception
     */
    public function getRateForCountry(string $countryCode, string $level = self::RATE_STANDARD) : float
    {
        $todayMidnight = new \DateTimeImmutable('today midnight');
        return $this->getRateForCountryOn($countryCode, $todayMidnight, $level);
    }

    /**
     * @param string $countryCode ISO-3166-1-alpha2 country code
     * @param DateTimeInterface $datetime
     * @param string $level
     * @return float
     * @throws Exception
     */
    public function getRateForCountryOn(string $countryCode, \DateTimeInterface $datetime, string $level = self::RATE_STANDARD) : float
    {
        $activePeriod = $this->resolvePeriod($countryCode, $datetime);
        return $activePeriod->getRate($level);
    }

}