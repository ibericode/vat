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

        $url = sprintf('https://api.ip2country.info/ip?%s', $ipAddress);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        $response = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 400 || $response === '') {
            return '';
        }

        $data = json_decode($response);
        return $data->countryCode;
    }
}
