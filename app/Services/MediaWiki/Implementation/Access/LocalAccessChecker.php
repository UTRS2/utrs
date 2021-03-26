<?php

namespace App\Services\MediaWiki\Implementation\Access;

use App\Services\MediaWiki\Api\MediaWikiApi;
use Mediawiki\DataModel\User;

class LocalAccessChecker extends BaseAccessChecker
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
        return $api
                ->getMediaWikiExtras()
                ->getBlockInfo($user->getName()) !== null;
    }

    public function getGroupsToCheckWithoutUser(): array
    {
        return [
            'admin',
            'checkuser',
            'oversight',
        ];
    }

    protected function fetchGroups(User $user): array
    {
        $groups = $user->getGroups();

        // different UTRS and mediawiki names
        if (in_array('sysop', $groups)) {
            $groups[] = 'admin';
        }

        return $groups;
    }
}