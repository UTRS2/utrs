<?php

namespace Tests\Feature\Appeal\Action;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHasUsers;

abstract class BaseAppealActionTest extends TestCase
{
    use RefreshDatabase;
    use TestHasUsers;
}
