<?php

namespace App\Services\MediaWiki\Api\Data;

interface Block
{
    public function getBlockTarget(): string;
    public function getBlockingUser(): string;
    public function getBlockReason(): string;
    public function getBlockTimestamp(): string;
}
