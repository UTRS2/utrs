<?php

namespace App\Services\MediaWiki\Implementation\Access;

use App\Services\MediaWiki\Api\MediaWikiApi;
use Mediawiki\DataModel\User;

class GlobalAccessChecker extends BaseAccessChecker
{
    /** @var MediaWikiApi */
    private $mediaWikiApi;

    public function __construct(MediaWikiApi $mediaWikiApi)
    {
        $this->mediaWikiApi = $mediaWikiApi;
    }

    protected function getApi(): MediaWikiApi
    {
        return $this->mediaWikiApi;
    }

    protected function isBlocked(User $user, MediaWikiApi $api): bool
    {
        return false;
    }

    public function getGroupsToCheckWithoutUser(): array
    {
        return [
            'steward',
            'staff',
        ];
    }

    protected function fetchGroups(User $user): array
    {
        return $this
            ->getApi()
            ->getMediaWikiExtras()
            ->getGlobalGroupMembership($user->getName());
    }
}