<?php

namespace App\Services\Acc\Api;

interface AccConfiguration
{
    /**
     * Checks if the ACC integration is enabled for the given wiki.
     * @param string $wiki Wiki database name
     * @return bool True if the specified wiki can have its appeals transferred to ACC, false otherwise
     */
    public function isEnabledForWiki(string $wiki): bool;

    /**
     * Returns the max range CIDR size (number after the slash) that can be appealed in UTRS. Ranges larger than that
     * should be forced to transfer to ACC. There is probably a better name than "cidr range size", but I'm not a
     * network engineer.
     *
     * @param int $ipVersion 4 if the checking for IPv4 ranges, 6 if checking IPv6 ranges
     * @return int max cidr range size to allow appeals in UTRS
     */
    public function getMaxAppealableRangeSize(int $ipVersion): int;
}
