<?php

namespace App\Services\MediaWiki\Api;

/**
 * Handle permissions configuration for a configured wiki.
 */
interface WikiPermissionHandler
{
    /**
     * @param string $action
     * @return array
     */
    public function getRequiredGroupsForAction(string $action): array;
}