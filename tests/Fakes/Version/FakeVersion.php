<?php

namespace Tests\Fakes\Version;

use App\Services\Version\Api\Version;

class FakeVersion implements Version
{
    public function getVersion(): string
    {
        // no need to bother with slow exec()s in testing, just return an empty string
        return '';
    }
}
