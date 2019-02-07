<?php

namespace Ibericode\Vat;

use DateTime;
use DateTimeInterface;
use Exception;

class Country
{
    const RATE_STANDARD = 'standard';
    const RATE_REDUCED = 'reduced';

    private $name;
    private $code;
    private $periods = [];

    public function __construct(string $code, string $name, array $periods = [])
    {
        $this->name = $name;
        $this->code = $code;

        // Ensure we have a DateTime for each period
        $this->periods = array_map(function($period) {
            if ($period['effective_from'] instanceof DateTimeInterface) {
                return $period;
            }

            $period['effective_from'] = new DateTime($period['effective_from']);
            return $period;
        }, $periods);

        // Sort periods by DateTime (DESC)
        usort($this->periods, function ($period1, $period2) {
            return $period1 > $period2 ? -1 : 1;
        });
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getCode() : string
    {
        return $this->code;
    }

    private function resolveRatesOn(DateTimeInterface $datetime) : array
    {
        // find first period larger than given datetime
        foreach ($this->periods AS $period) {
            if ($datetime > $period['effective_from']) {
                return $period['rates'];
            }
        }

        throw new \Exception('Unable to find a rate applicable at that date.');
    }

    public function getRate(string $level = self::RATE_STANDARD) : float
    {
        $todayMidnight = new \DateTime('today midnight');
        return $this->getRateOn($todayMidnight, $level);
    }

    public function getRateOn(\DateTimeInterface $datetime, string $level = self::RATE_STANDARD) : float
    {
        $rates = $this->resolveRatesOn($datetime);

        if (!isset($rates[$level])) {
            throw new \Exception('Invalid rate.');
        }

        return $rates[$level];
    }

    public function isEU() : bool
    {
        $eu = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK',
            'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HU', 'HR',
            'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL',
            'PT', 'RO', 'SE', 'SI', 'SK'];

        return in_array($this->code, $eu);
    }
}