<?php

namespace Ibericode\Vat\Geolocation;

interface GeolocatorInterface
{
    /**
     * @param string $ipAddress The IP address to geolocate
     * @return string A ISO-3166-1-alpha2 country code or an empty string on failure
     */
    public function locateIpAddress(string $ipAddress): string;
}
