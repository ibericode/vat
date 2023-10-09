<?php

namespace Ibericode\Vat\Geolocation;

/**
 * Class IP2C
 *
 * Geo-locates an IP address using ip2c.org
 *
 * @package Ibericode\Vat\Geolocation
 */
class IP2C implements GeolocatorInterface
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

        $url = sprintf('https://ip2c.org/%s', urlencode($ipAddress));
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

        $parts = explode(';', $response);
        return $parts[1] === 'ZZ' ? '' : $parts[1];
    }
}
