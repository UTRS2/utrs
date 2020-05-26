<?php

namespace App\Utils;

use Illuminate\Support\Str;

/**
 * Provides utilities for working with IP addresses and ranges
 */
final class IPUtils
{
    /**
     * Sanity checks if given string looks like an IP range
     * @param string $string - string to test
     * @return bool true if $string looks like an IP range
     */
    public static function isIpRange(string $string) {
        if (!Str::contains($string, '/')) {
            return false;
        }

        $parts = explode('/', $string);
        return sizeof($parts) === 2 && filter_var($parts[0], FILTER_VALIDATE_IP) !== false && filter_var($parts[1], FILTER_VALIDATE_INT) !== null;
    }
}
