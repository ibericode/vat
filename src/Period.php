<?php

declare(strict_types=1);

namespace Ibericode\Vat;

use InvalidArgumentException;
use DateTimeInterface;

class Period {
    const RATE_STANDARD = 'standard';
    const RATE_REDUCED = 'reduced';

    private $effectiveFrom;
    private $rates = [];

    public function __construct(DateTimeInterface $effectiveFrom, array $rates)
    {
        $this->effectiveFrom = $effectiveFrom;
        $this->rates = $rates;
    }

    public function getEffectiveFrom() : DateTimeInterface
    {
        return $this->effectiveFrom;
    }

    public function getRate(string $level = self::RATE_STANDARD) : float
    {
        if (!isset($this->rates[$level])) {
            throw new InvalidArgumentException("Invalid rate level: {$level}");
        }

        return $this->rates[$level];
    }
}