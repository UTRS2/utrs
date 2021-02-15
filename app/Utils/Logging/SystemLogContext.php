<?php

namespace App\Utils\Logging;

class SystemLogContext implements LogContext
{
    public function getUserId(): int
    {
        return 0;
    }

    public function getIpAddress(): string
    {
        return '127.0.0.1';
    }

    public function getUserAgent(): string
    {
        return 'DB/Laravel';
    }
}