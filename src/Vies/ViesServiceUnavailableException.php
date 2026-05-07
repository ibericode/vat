<?php

namespace Ibericode\Vat\Vies;

/**
 * Thrown when the VIES service refused to give a definitive answer.
 *
 * This wraps the operationally-distinct error codes returned by VIES that
 * indicate the service is temporarily unable to respond — as opposed to
 * a definitive "VAT number is invalid". Callers should treat these as
 * transient and retry with backoff rather than recording the VAT as invalid.
 *
 * Covers VIES error codes:
 *   - MS_UNAVAILABLE           (the member-state's node is down)
 *   - SERVICE_UNAVAILABLE      (VIES global outage)
 *   - TIMEOUT                  (member-state node timed out)
 *   - MS_MAX_CONCURRENT_REQ    (per-member-state throttling)
 *   - GLOBAL_MAX_CONCURRENT_REQ (global throttling)
 *   - IP_BLOCKED               (caller IP temporarily blocked)
 */
class ViesServiceUnavailableException extends ViesException
{
    private const TRANSIENT_FAULT_STRINGS = [
        'MS_UNAVAILABLE',
        'SERVICE_UNAVAILABLE',
        'TIMEOUT',
        'MS_MAX_CONCURRENT_REQ',
        'GLOBAL_MAX_CONCURRENT_REQ',
        'IP_BLOCKED',
    ];

    public static function isTransientFault(string $faultString): bool
    {
        $upper = strtoupper($faultString);
        foreach (self::TRANSIENT_FAULT_STRINGS as $needle) {
            if (str_contains($upper, $needle)) {
                return true;
            }
        }

        return false;
    }
}
