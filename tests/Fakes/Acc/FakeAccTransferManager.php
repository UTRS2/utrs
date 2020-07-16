<?php

namespace Tests\Fakes\Acc;

use App\Appeal;
use App\Services\Acc\Api\AccTransferManager;
use Illuminate\Support\Str;

class FakeAccTransferManager implements AccTransferManager
{
    /** @var FakeAccIntegration */
    private $acc;

    public function __construct(FakeAccIntegration $acc)
    {
        $this->acc = $acc;
    }

    public function shouldAllowTransfer(Appeal $appeal): bool
    {
        return Str::startsWith($appeal->block_target, ['ACC Allow', 'ACC Force']);
    }

    public function shouldRequireTransfer(Appeal $appeal): bool
    {
        return Str::startsWith($appeal->block_target, ['ACC Force']);
    }
}
