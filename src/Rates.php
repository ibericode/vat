<?php
declare(strict_types=1);

namespace Ibericode\Vat;

use DateTime;
use DateTimeInterface;

use Ibericode\Vat\Clients\ClientException;
use Ibericode\Vat\Clients\IbericodeVatRatesClient;
use Ibericode\Vat\Clients\Client;

class Rates {
    const RATE_STANDARD = 'standard';

    private $rates = [];
    private $client;
    private $storagePath;
    private $ttl;

    public function __construct(string $storagePath = '', int $ttl = 12 * 3600, Client $client = null)
    {
        $this->ttl = $ttl;
        $this->storagePath = $storagePath;
        $this->client = $client;
    }

    private function load()
    {
        if (count($this->rates) > 0) {
            return;
        }

        if ($this->storagePath !== '' && file_exists($this->storagePath)) {
            $this->loadFromFile();

            if (filemtime($this->storagePath) > (time() - $this->ttl)) {
                return;
            }
        }

        try {
            $this->client = $this->client ?: new IbericodeVatRatesClient();
            $this->rates = $this->client->fetch();
        } catch(ClientException $e) {
            // local file is due for a refresh, but service seems down
            if (count($this->rates) > 0) {
                return;
            }

            throw $e;
        }

        // Sort periods by DateTime (DESC)
        foreach ($this->rates as $country => $periods) {
            usort($this->rates[$country], function (Period $period1, Period $period2) {
                return $period1->getEffectiveFrom() > $period2->getEffectiveFrom() ? -1 : 1;
            });
        }

        if ($this->storagePath !== '') {
            file_put_contents($this->storagePath, serialize($this->rates));
        }
    }

    private function loadFromFile()
    {
        $contents = file_get_contents($this->storagePath);
        $this->rates = unserialize($contents, [
            'allowed_classes' => [
                Period::class,
                DateTime::class
            ]
        ]);
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
        return $this->getRateForCountryOnDate($countryCode, $todayMidnight, $level);
    }

    /**
     * @param string $countryCode ISO-3166-1-alpha2 country code
     * @param DateTimeInterface $datetime
     * @param string $level
     * @return float
     * @throws Exception
     */
    public function getRateForCountryOnDate(string $countryCode, \DateTimeInterface $datetime, string $level = self::RATE_STANDARD) : float
    {
        $activePeriod = $this->resolvePeriod($countryCode, $datetime);
        return $activePeriod->getRate($level);
    }

}