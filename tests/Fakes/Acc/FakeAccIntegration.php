<?php

namespace Tests\Fakes\Acc;

use App\Services\Acc\Api\AccConfiguration;
use App\Services\Acc\Api\AccIntegration;
use App\Services\Acc\Api\AccTransferManager;
use App\Services\Acc\Implementation\RealAccConfiguration;
use Illuminate\Config\Repository as ConfigurationRepository;

class FakeAccIntegration implements AccIntegration
{
    /** @var ConfigurationRepository */
    private $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    public function getIntegrationConfiguration(): AccConfiguration
    {
        return new RealAccConfiguration($this->configurationRepository);
    }

    public function getTransferManager(): AccTransferManager
    {
        return new FakeAccTransferManager($this);
    }
}
