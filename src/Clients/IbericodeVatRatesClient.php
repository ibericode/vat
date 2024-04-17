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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        $body = (string) curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === '' || $status >= 400) {
            throw new ClientException("Error fetching rates from {$url}.");
        }

        return $this->parseResponse($body);
    }

    private function parseResponse(string $response_body): array
    {
        $result = json_decode($response_body, false);

        $return = [];
        foreach ($result->items as $country => $periods) {
            foreach ($periods as $i => $period) {
                $periods[$i] = new Period(new \DateTimeImmutable($period->effective_from), (array) $period->rates);
            }

            $return[$country] = $periods;
        }

        return $return;
    }
}
