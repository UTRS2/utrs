<?php

namespace App\Services\Acc\Implementation;

use App\Appeal;
use App\Services\Acc\Api\AccTransferManager;
use App\Utils\IPUtils;

class RealAccTransferManager implements AccTransferManager
{
    /** @var RealAccIntegration */
    private $acc;

    public function __construct(RealAccIntegration $acc)
    {
        $this->acc = $acc;
    }

    private function getAppealTargetRange(Appeal $appeal)
    {
        if (IPUtils::isIpRange($appeal->block_target)) {
            return $appeal->block_target;
        }

        return null;
    }

    public function shouldAllowTransfer(Appeal $appeal): bool
    {
        if (!$this->acc->getIntegrationConfiguration()->isEnabledForWiki($appeal->wiki)) {
            return false;
        }

        if (!$this->getAppealTargetRange($appeal)) {
            return false;
        }

        return true;
    }

    public function shouldRequireTransfer(Appeal $appeal): bool
    {
        if (!$this->shouldAllowTransfer($appeal)) {
            return false;
        }

        $target = $this->getAppealTargetRange($appeal);
        $rangeSize = IPUtils::getRangeCidrSize($target);
        $maxRangeSize = $this->acc->getIntegrationConfiguration()->getMaxAppealableRangeSize(
            IPUtils::isIPv6($target) ? 6 : 4);

        return $maxRangeSize > $rangeSize;
    }
}
