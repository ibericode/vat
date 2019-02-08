<?php
declare(strict_types=1);

namespace Ibericode\Vat;

use DateTimeImmutable;
use DateTimeInterface;
use Ibericode\Vat\Exceptions\Exception;

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
        $this->periods = $periods;

        // Sort periods by DateTime (DESC)
        usort($this->periods, function (Period $period1, Period $period2) {
            return $period1->getEffectiveFrom() > $period2->getEffectiveFrom() ? -1 : 1;
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

    private function resolvePeriod(DateTimeInterface $datetime) : Period
    {
        // find first active period (because periods are sorted)
        foreach ($this->periods AS $period) {
            if ($datetime >= $period->getEffectiveFrom()) {
                return $period;
            }
        }

        throw new Exception("Unable to find a rate for date {$datetime->format(DATE_ATOM)}.");
    }

    public function getRate(string $level = self::RATE_STANDARD) : float
    {
        $todayMidnight = new \DateTimeImmutable('today midnight');
        return $this->getRateOn($todayMidnight, $level);
    }

    public function getRateOn(\DateTimeInterface $datetime, string $level = self::RATE_STANDARD) : float
    {
        $activePeriod = $this->resolvePeriod($datetime);
        return $activePeriod->getRate($level);
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