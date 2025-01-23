<?php

declare(strict_types=1);

namespace Ibericode\Vat;

use InvalidArgumentException;
use DateTimeInterface;

/**
 * Class Period
 *
 * @package Ibericode\Vat
 * @internal
 */
class Period
{
    private $effectiveFrom;
    private $rates = [];
    private $exceptions = [];

    public function __construct(DateTimeInterface $effectiveFrom, array $rates, array $exceptions = [])
    {
        $this->effectiveFrom = $effectiveFrom;
        $this->rates = $rates;
        $this->exceptions = $exceptions;
    }

    public function getEffectiveFrom(): DateTimeInterface
    {
        return $this->effectiveFrom;
    }

    public function getRate(string $level, ?string $postcode = null): float
    {
        if (!isset($this->rates[$level])) {
            throw new InvalidArgumentException("Invalid rate level: {$level}");
        }

        return $this->getExceptionRate($level, $postcode) ?? $this->rates[$level];
    }

    private function getExceptionRate(string $level, ?string $postcode): ?float
    {
        if (!$postcode) {
            return null;
        }

        foreach ($this->exceptions as $exception) {
            $exception = (array) $exception;
            if (preg_match('/^'.$exception['postcode'].'$/', $postcode)) {
                return $exception[$level] ?? $exception[Rates::RATE_STANDARD];
            }
        }

        return null;
    }

}
