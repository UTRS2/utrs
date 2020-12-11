<?php

namespace Tests;

use App\Services\Version\Api\Version;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Fakes\Version\FakeVersion;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(Version::class, FakeVersion::class);
    }
}
