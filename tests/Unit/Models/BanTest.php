<?php

namespace Tests\Unit\Models;

use App\Models\Ban;
use App\Utils\IPUtils;
use Tests\TestCase;

class BanTest extends TestCase
{
    public function test_get_targets_to_check_with_normal_text()
    {
        $this->assertEquals(['1234'], Ban::getTargetsToCheck('1234'));
    }

    public function test_get_targets_to_check_with_multiple_items()
    {
        $this->assertEquals(['1234', '5678'], Ban::getTargetsToCheck('1234', '5678'));
        $this->assertEquals(['1234', '5678'], Ban::getTargetsToCheck(['1234', '5678']));
    }

    public function test_get_targets_to_check_with_ips()
    {
        $this->assertEquals(array_merge(['5.6.7.8'], IPUtils::getAllParentRanges('5.6.7.8')), Ban::getTargetsToCheck('5.6.7.8'));
        $this->assertEquals(array_merge(['5.6.7.8'], IPUtils::getAllParentRanges('5.6.7.8')), Ban::getTargetsToCheck('5.6.7.8/32'));
        $this->assertEquals(array_merge(['5.6.7.8'], IPUtils::getAllParentRanges('5.6.7.8')), Ban::getTargetsToCheck('5.6.7.8/24'));
    }
}
