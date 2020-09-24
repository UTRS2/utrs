<?php

namespace App\Utils;

use Illuminate\Support\Str;
use Wikimedia\IPUtils as WikimediaIpUtils;
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
     * Get a normalized representation of a CIDR range
     * For example, 192.168.020.130/24 becomes 192.168.20.0/24
     * @param string $range CIDR range to normalize
     * @return string
     */
    public static function normalizeRange(string $range)
    {
        // sanitizeRange gives the start of the range, ie 192.168.100.130/24 becomes 192.168.100.0/24
        // sanitizeIp removes all anomalies like leading zeroes etc and normalizes spelling
        // both work on ranges, so let's normalize using both
        return WikimediaIpUtils::sanitizeRange(WikimediaIpUtils::sanitizeIP($range));
    }

    public static function cutCidrRangePart(string $range)
    {
        [ $ip, ] = explode('/', $range);
        return $ip;
    }

    /**
     * Checks if given IP is inside a given IP range. Convenience bridge to confusingly named Symfony class.
     * @param string $range ip range to test
     * @param string $ip ip address to test
     * @return bool true if $ip is inside range $range
     */
    public static function isIpInsideRange(string $range, string $ip)
    {
        return SymfonyIpUtils::checkIp($ip, $range);
    }

    /**
     * Gets the CIDR range size (number after the slash) for a given range
     * There is probably a better name than "cidr range size", but I'm not a network engineer
     * @param string $range cidr range
     * @return int
     */
    public static function getRangeCidrSize(string $range)
    {
        [ , $size ] = WikimediaIpUtils::parseCIDR($range);
        return $size;
    }

    /**
     * Checks if the given ip address or range is using IPv6. Convenience bridge to confusingly named Wikimedia IP util class.
     * @param string $ipOrRange ip or range to check
     * @return bool true if $ipOrRange is using ipv6, false otherwise
     */
    public static function isIPv6(string $ipOrRange)
    {
        return WikimediaIpUtils::isIPv6($ipOrRange);
    }
}
