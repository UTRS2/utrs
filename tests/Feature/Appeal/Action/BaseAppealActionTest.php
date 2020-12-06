<?php

namespace Tests\Feature\Appeal\Action;

use Tests\TestCase;
use Tests\Traits\SetupDatabaseForTesting;
use Tests\Traits\TestHasUsers;

abstract class BaseAppealActionTest extends TestCase
{
    use SetupDatabaseForTesting;
    use TestHasUsers;
}
