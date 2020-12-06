<?php

namespace Tests\Feature\Appeal\Action;

use Tests\Traits\SetupDatabaseForTesting;
use Tests\TestCase;
use Tests\Traits\TestHasUsers;

abstract class BaseAppealActionTest extends TestCase
{
    use SetupDatabaseForTesting;
    use TestHasUsers;
}
