<?php

namespace App\Utils;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils as SymfonyIpUtils;

/**
 * Provides utilities for working with IP addresses and ranges
 */
final class IPUtils
{
    /**
     * Checks if a given string is an IP address
     * @param string $string string to test
     * @return bool true if $string is an IP address
     */
    public static function isIp(string $string)
    {
        return filter_var($string, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Sanity checks if given string looks like an IP range
     * @param string $string string to test
     * @return bool true if $string looks like an IP range
     */
    public static function isIpRange(string $string)
    {
        if (!Str::contains($string, '/')) {
            return false;
        }

        $parts = explode('/', $string);
        return sizeof($parts) === 2 && self::isIp($parts[0]) && filter_var($parts[1], FILTER_VALIDATE_INT) !== null;
    }

    /**
     * Checks if given IP is inside a given IP range. Convenience bridge to confusingly named Symfony method.
     * @param string $range ip range to test
     * @param string $ip ip address to test
     * @return bool true if $ip is inside range $range
     */
    public static function isIpInsideRange(string $range, string $ip)
    {
        return SymfonyIpUtils::checkIp($ip, $range);
    }
}
