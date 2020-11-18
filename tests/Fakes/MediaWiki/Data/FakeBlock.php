<?php

namespace Tests\Fakes\MediaWiki\Data;

use App\Services\MediaWiki\Api\Data\Block;

class FakeBlock implements Block
{
    /** @var array */
    private $user;

    public function __construct(array $user)
    {
        $this->user = $user;
    }

    public function getBlockTarget(): string
    {
        return $this->user['name'];
    }

    public function getBlockingUser(): string
    {
        return 'Blocking administrator';
    }

    public function getBlockReason(): string
    {
        return 'Block reason';
    }

    public function getBlockTimestamp(): string
    {
        return $this->user['registration'];
    }
}
