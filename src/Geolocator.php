<?php

declare(strict_types=1);

namespace Ibericode\Vat;

class Geolocator {

    public function __construct()
    {

    }

    public function locateIpAddress(string $ipAddress) : string
    {
        if ($ipAddress === '') {
            return '';
        }

        $url = sprintf('https://ip2c.org/%s', urlencode($ipAddress));

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        $response_body = curl_exec($curl_handle);
        curl_close($curl_handle);

        if ($response_body === null || $response_body === '') {
            return '';
        }

        $parts = explode( ';', $response_body );
        return $parts[1] === 'ZZ' ? '' : $parts[1];
    }

}