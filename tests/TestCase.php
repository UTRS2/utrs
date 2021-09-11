<?php

namespace Tests;

use App\Services\Version\Api\Version;
use Taavi\LaravelTorblock\Service\FakeTorExitNodeService;
use Taavi\LaravelTorblock\Service\TorExitNodeService;
use Tests\Fakes\Version\FakeVersion;
use Tests\Traits\CreatesApplication;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(Version::class, FakeVersion::class);
        $this->app->bind(TorExitNodeService::class, FakeTorExitNodeService::class);
    }
}
