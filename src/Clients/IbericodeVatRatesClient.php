<?php

declare(strict_types=1);

namespace Ibericode\Vat\Clients;

use Ibericode\Vat\Period;

class IbericodeVatRatesClient implements Client
{
    /**
     * @throws ClientException
     *
     * @return array
     */
    public function fetch(): array
    {
        $url = 'https://raw.githubusercontent.com/ibericode/vat-rates/master/vat-rates.json';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_TIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = (string) curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        if ($body === '' || $status >= 400) {
            throw new ClientException("Error fetching rates from {$url}.");
        }

        return $this->parseResponse($body);
    }

    private function parseResponse(string $response_body): array
    {
        $result = json_decode($response_body, false);

        if (!is_object($result) || !isset($result->items) || !is_object($result->items)) {
            throw new ClientException('Malformed response from VAT rates service.');
        }

        $return = [];
        foreach ($result->items as $country => $periods) {
            if (!is_array($periods)) {
                throw new ClientException("Malformed periods for country {$country}.");
            }

            foreach ($periods as $i => $period) {
                if (!is_object($period) || !isset($period->effective_from, $period->rates)) {
                    throw new ClientException("Malformed period entry for country {$country}.");
                }
                $periods[$i] = new Period(new \DateTimeImmutable($period->effective_from), (array) $period->rates);
            }

            $return[$country] = $periods;
        }

        return $return;
    }
}
