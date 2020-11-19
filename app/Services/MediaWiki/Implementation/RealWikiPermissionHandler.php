<?php

namespace App\Services\MediaWiki\Implementation;

use App\Services\MediaWiki\Api\WikiPermissionHandler;

class RealWikiPermissionHandler implements WikiPermissionHandler
{
    /** @var array */
    private $finalValues;

    public function __construct(array $globalSettings, array $overrides)
    {
        $this->finalValues = $globalSettings;
        foreach ($overrides as $key => $value) {
            $this->finalValues[$key] = $value;
        }
    }

    public function getRequiredGroupsForAction(string $action): array
    {
        return $this->finalValues[$action];
    }
}