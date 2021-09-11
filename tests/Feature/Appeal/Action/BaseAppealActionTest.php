<?php

namespace Tests\Feature\Appeal\Action;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestHasUsers;

abstract class BaseAppealActionTest extends TestCase
{
    use DatabaseMigrations;
    use TestHasUsers;
}
