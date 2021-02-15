<?php

namespace App\Utils\Logging;

use Illuminate\Http\Request;

class RequestLogContext implements LogContext
{
    /** @var int */
    private $userId;

    /** @var string */
    private $ipAddress;

    /** @var string */
    private $userAgent;

    public function __construct(Request $request)
    {
        $this->userId = $request->user() ? $request->user()->id : -1;
        $this->ipAddress = $request->ip();
        $this->userAgent = $request->userAgent() . ' ' . $request->header('Accept-Language');
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }
}