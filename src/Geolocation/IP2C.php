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

        $url = 'https://ip2c.org/' . urlencode($ipAddress);
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

        $parts = explode(';', $response);
        return $parts[1] === 'ZZ' ? '' : $parts[1];
    }
}
