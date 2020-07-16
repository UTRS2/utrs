<?php

namespace App\Services\Acc\Api;

interface AccIntegration
{
    public function getIntegrationConfiguration(): AccConfiguration;

    public function getTransferManager(): AccTransferManager;
}
