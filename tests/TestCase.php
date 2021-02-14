<?php

namespace Tests;

use Tests\Traits\CreatesApplication;
use App\Services\Version\Api\Version;
use Tests\Traits\CreatesApplication;
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
