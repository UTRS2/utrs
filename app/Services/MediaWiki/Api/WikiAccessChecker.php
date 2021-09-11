<?php

namespace App\Services\MediaWiki\Api;

/**
 * Check what levels of access does a user have in a specific wiki.
 */
interface WikiAccessChecker
{
    /**
     * Check the list of UTRS-supported groups that the specified user belongs in.
     * @param string $username
     * @return string[]
     */
    public function getUserGroups(string $username): array;

    /**
     * Return the list of groups that this checker will check for.
     * @return string[]
     */
    public function getGroupsToCheck(): array;
}