<?php

namespace App\Utils\Logging;

interface LogContext
{
    public function getUserId(): int;
    public function getIpAddress(): string;
    public function getUserAgent(): string;
}