<?php

namespace Ibericode\Vat\Geolocation;

/**
 * Class IP2Country
 *
 * Geo-locates an IP address using ip2country.info
 *
 * @package Ibericode\Vat\Geolocation
 */
class IP2Country implements GeolocatorInterface
{
    /**
     * @param string $ipAddress
     * @return string
     */
    public function locateIpAddress(string $ipAddress): string
    {
        if ($ipAddress === '') {
            return '';
        }

        $url = 'https://api.ip2country.info/ip?' . $ipAddress;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_TIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($code >= 400 || $response === '') {
            return '';
        }

        $data = json_decode($response);
        return $data->countryCode;
    }
}
