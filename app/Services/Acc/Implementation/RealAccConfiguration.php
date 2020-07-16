<?php

namespace App\Services\Acc\Implementation;

use App\Services\Acc\Api\AccConfiguration;
use Illuminate\Config\Repository as ConfigurationRepository;

class RealAccConfiguration implements AccConfiguration
{
    /** @var string[] */
    private $wikis;

    /** @var int */
    private $maxRangeSizeIpv4;

    /** @var int */
    private $maxRangeSizeIpv6;

    public function __construct(ConfigurationRepository $repository)
    {
        $this->wikis = $repository->get('acc.enabled_for_wikis');
        $this->maxRangeSizeIpv4 = $repository->get('acc.max_sizes_to_appeal.ipv4');
        $this->maxRangeSizeIpv6 = $repository->get('acc.max_sizes_to_appeal.ipv6');
    }

    public function isEnabledForWiki(string $wiki): bool
    {
        return in_array($wiki, $this->wikis);
    }

    public function getMaxAppealableRangeSize(int $ipVersion): int
    {
        return $ipVersion == 4 ? $this->maxRangeSizeIpv4 : $this->maxRangeSizeIpv6;
    }
}
