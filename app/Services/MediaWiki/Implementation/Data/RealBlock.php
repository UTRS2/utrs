<?php

namespace App\Services\MediaWiki\Implementation\Data;

use App\Services\MediaWiki\Api\Data\Block;

class RealBlock implements Block
{
    /** @var string */
    private $blockTarget;

    /** @var string */
    private $blockingUser;

    /** @var string */
    private $blockReason;

    /** @var string */
    private $blockTimestamp;

    /**
     * Creates a RealBlock.
     * @param string $blockTarget
     * @param string $blockingUser
     * @param string $blockReason
     * @param string $blockTimestamp
     */
    public function __construct(string $blockTarget, string $blockingUser, string $blockReason, string $blockTimestamp)
    {
        $this->blockTarget = $blockTarget;
        $this->blockingUser = $blockingUser;
        $this->blockReason = $blockReason;
        $this->blockTimestamp = $blockTimestamp;
    }

    public static function fromArray($blockData)
    {
        return new RealBlock(
            $blockData['user'],
            $blockData['by'],
            $blockData['reason'],
            $blockData['timestamp'],
        );
    }

    /**
     * @return string
     */
    public function getBlockTarget(): string
    {
        return $this->blockTarget;
    }

    /**
     * @return string
     */
    public function getBlockingUser(): string
    {
        return $this->blockingUser;
    }

    /**
     * @return string
     */
    public function getBlockReason(): string
    {
        return $this->blockReason;
    }

    /**
     * @return string
     */
    public function getBlockTimestamp(): string
    {
        return $this->blockTimestamp;
    }
}
