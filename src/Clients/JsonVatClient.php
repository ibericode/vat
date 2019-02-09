<?php
declare(strict_types=1);

namespace Ibericode\Vat\Clients;

use Ibericode\Vat\Period;

class JsonVatClient implements Client
{

    /**
     * @throws ClientException
     *
     * @return array
     */
    public function fetch() : array 
    {
        $url = 'https://jsonvat.com/';

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
            throw new ClientException( "Error fetching rates from {$url}.");
        }

        return $this->parseResponse($body);

    }

    private function parseResponse(string $response_body) : array
    {
        $data = json_decode($response_body, false);

        $return = [];
        foreach ($data->rates as $country_rates) {
            $periods = [];

            foreach ($country_rates->periods as $period) {
                $periods[] = new Period(new \DateTimeImmutable($period->effective_from), (array) $period->rates);
            }

            $return[$country_rates->country_code] = $periods;
        }

        return $return;
    }
}
