<?php

namespace App\Services\Version\Api;

/**
 * Service for utilities for dealing with UTRS versioning.
 */
interface Version
{
    public function getVersion(): string;
}
