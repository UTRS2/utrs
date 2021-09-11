<?php

namespace Tests\Fakes\MediaWiki;

use App\Services\MediaWiki\Api\Data\Block;
use App\Services\MediaWiki\Api\MediaWikiExtras;
use RuntimeException;
use Tests\Fakes\MediaWiki\Data\FakeBlock;

class FakeMediaWikiExtras implements MediaWikiExtras
{
    private $api;

    public function __construct(FakeMediaWikiApi $api)
    {
        $this->api = $api;
    }

    public function canEmail(string $username): bool
    {
        return false;
    }

    public function sendEmail(string $username, string $title, string $content): bool
    {
        throw new RuntimeException('Not supported in this fake');
    }

    public function getBlockInfo(string $target, int $appealId = -1, string $searchKey = null): ?Block
    {
        $user = $this->api->getRepository()->getFakeUser($this->api->getWikiName(), $target);
        return $user['blocked']
            ? new FakeBlock($user)
            : null;
    }

    public function getGlobalBlockInfo(string $target, int $appealId = -1): ?Block
    {
        return $this->api->getWikiName() === 'global' ? $this->getBlockInfo($target, $appealId) : null;
    }

    public function getGlobalGroupMembership(string $userName): array
    {
        $user = $this->api->getRepository()->getFakeUser($this->api->getWikiName(), $userName);
        $globalrights = [];

        if (in_array('steward', $user['rights'])) {
            $globalrights[] = 'steward';
        }

        if (in_array('staff', $user['rights'])) {
            $globalrights[] = 'staff';
        }
        return $globalrights;
    }
}
