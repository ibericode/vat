<?php

declare(strict_types=1);

namespace Ibericode\Vat;

use DateTimeInterface;
use DateTimeImmutable;
use Ibericode\Vat\Clients\ClientException;
use Ibericode\Vat\Clients\IbericodeVatRatesClient;
use Ibericode\Vat\Clients\Client;

class Rates
{
    public const RATE_STANDARD = 'standard';

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
    public function __construct(string $storagePath, int $refreshInterval = 12 * 3600, ?Client $client = null)
    {
        $this->refreshInterval = $refreshInterval;
        $this->storagePath = $storagePath;
        $this->client = $client;
    }

    private function load(): void
    {
        if (count($this->rates) > 0) {
            return;
        }

        if ($this->storagePath !== '' && \is_file($this->storagePath)) {
            $this->loadFromFile();

            // bail early if file is still valid
            // TODO: Store timestamp in file, so we're safe from fs modifications
            if (filemtime($this->storagePath) > (time() - $this->refreshInterval)) {
                return;
            }
        }

        $this->loadFromRemote();
    }

    private function loadFromFile(): void
    {
        $contents = file_get_contents($this->storagePath);
        if ($contents === false || $contents === '') {
            throw new Exception("Empty rates cache file");
        }

        $data = json_decode($contents, true);
        if (!is_array($data)) {
            throw new Exception("Malformed rates cache file");
        }

        $rates = [];
        foreach ($data as $country => $periods) {
            if (!is_array($periods)) {
                throw new Exception("Malformed rates cache file");
            }

            foreach ($periods as $period) {
                if (!is_array($period) || !isset($period['effective_from'], $period['rates']) || !is_array($period['rates'])) {
                    throw new Exception("Malformed rates cache file");
                }

                $rates[$country][] = new Period(
                    new DateTimeImmutable($period['effective_from']),
                    $period['rates']
                );
            }
        }

        $this->rates = $rates;
    }

    /**
     * @return array<string, array<int, array{effective_from: string, rates: array<string, float>}>>
     */
    private function ratesToArray(): array
    {
        $out = [];
        foreach ($this->rates as $country => $periods) {
            foreach ($periods as $period) {
                /** @var Period $period */
                $out[$country][] = [
                    'effective_from' => $period->getEffectiveFrom()->format(\DateTimeInterface::ATOM),
                    'rates' => $period->getRates(),
                ];
            }
        }

        return $out;
    }

    private function loadFromRemote(): void
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
        foreach (array_keys($this->rates) as $country) {
            usort($this->rates[$country], function (Period $period1, Period $period2) {
                return $period2->getEffectiveFrom() <=> $period1->getEffectiveFrom();
            });
        }

        // update local file with updated rates
        if ($this->storagePath !== '') {
            $payload = json_encode($this->ratesToArray(), JSON_THROW_ON_ERROR);
            $this->writeStorageAtomic($payload);
        }
    }

    private function writeStorageAtomic(string $contents): void
    {
        $tmp = $this->storagePath . '.' . bin2hex(random_bytes(6)) . '.tmp';
        if (file_put_contents($tmp, $contents) === false) {
            return;
        }

        if (!@rename($tmp, $this->storagePath)) {
            @unlink($tmp);
        }
    }

    private function resolvePeriod(string $countryCode, DateTimeInterface $datetime): Period
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
    public function getRateForCountry(string $countryCode, string $level = self::RATE_STANDARD): float
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
    public function getRateForCountryOnDate(string $countryCode, \DateTimeInterface $datetime, string $level = self::RATE_STANDARD): float
    {
        return $this->resolvePeriod($countryCode, $datetime)->getRate($level);
    }
}
