<?php
declare(strict_types=1);

namespace Ibericode\Vat;

use DateTime;
use DateTimeInterface;
use DateTimeImmutable;

use Ibericode\Vat\Clients\ClientException;
use Ibericode\Vat\Clients\IbericodeVatRatesClient;
use Ibericode\Vat\Clients\Client;

class Rates
{
    const RATE_STANDARD = 'standard';

    private $rates = [];

    /**
     * @var Client|null
     */
    private $client;

    /**
     * @var string
     */
    private $storagePath;

    /**
     * @var int
     */
    private $refreshInterval;

    /**
     * Rates constructor.
     *
     * @param string $storagePath Path to file for storing VAT rates.
     * @param int $refreshInterval How often to check for new VAT rates. Defaults to every 12 hours.
     * @param Client|null $client The VAT client to use.
     */
    public function __construct(string $storagePath, int $refreshInterval = 12 * 3600, Client $client = null)
    {
        $this->refreshInterval = $refreshInterval;
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

            // bail early if file is still valid
            // TODO: Store timestamp in file, so we're safe from fs modifications
            if (filemtime($this->storagePath) > (time() - $this->refreshInterval)) {
                return;
            }
        }

        $this->loadFromRemote();
    }

    private function loadFromFile()
    {
        $contents = file_get_contents($this->storagePath);
        $data = unserialize($contents, [
            'allowed_classes' => [
                Period::class,
                DateTimeImmutable::class
            ]
        ]);

        if (is_array($data)) {
            $this->rates = $data;
        }
    }

    private function loadFromRemote()
    {
        try {
            $this->client = $this->client ?: new IbericodeVatRatesClient();
            $this->rates = $this->client->fetch();
        } catch (ClientException $e) {
            // this property could have been populated from the local filesystem at this stage
            // this ensures the application using this package keeps on running even if the VAT rates service is down
            if (count($this->rates) > 0) {
                return;
            }

            throw $e;
        }

        // sort periods by DateTime so that later periods come first
        foreach ($this->rates as $country => $periods) {
            usort($this->rates[$country], function (Period $period1, Period $period2) {
                return $period1->getEffectiveFrom() > $period2->getEffectiveFrom() ? -1 : 1;
            });
        }

        // update local file with updated rates
        if ($this->storagePath !== '') {
            file_put_contents($this->storagePath, serialize($this->rates));
        }
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
