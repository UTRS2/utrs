<?php

namespace Tests\Unit\Utils;

use App\Utils\IPUtils;
use PHPUnit\Framework\TestCase;

class IPUtilsTest extends TestCase
{
    public function test_detects_ipv4_ranges()
    {
        $this->assertTrue(IPUtils::isIpRange('10.1.1.0/24'));
        $this->assertTrue(IPUtils::isIpRange('10.20.16.0/21'));
        $this->assertTrue(IPUtils::isIpRange('10.66.23.123/32'));
    }

    public function test_detects_ipv6_ranges()
    {
        $this->assertTrue(IPUtils::isIpRange('2001:db8::/32'));
        $this->assertTrue(IPUtils::isIpRange('::ffff:0:0:0/96'));
    }

    public function test_does_not_detect_ipv4_addresses_as_ranges()
    {
        $this->assertFalse(IPUtils::isIpRange('10.20.20.100'));
        $this->assertFalse(IPUtils::isIpRange('10.66.23.123'));
    }

    public function test_does_not_detect_ipv6_addresses_as_ranges()
    {
        $this->assertFalse(IPUtils::isIpRange('2606:4700:4700::1111'));
        $this->assertFalse(IPUtils::isIpRange('2400:cb00:2048:1::c629:d7a2'));
    }

    public function test_does_not_detect_random_gibberish_as_ranges()
    {
        $this->assertFalse(IPUtils::isIpRange('I like eating potatoes.'));
        $this->assertFalse(IPUtils::isIpRange('I like/eating potatoes.'));
        $this->assertFalse(IPUtils::isIpRange('I like/12'));
        $this->assertFalse(IPUtils::isIpRange('I like/eating potatoes/because/they/are/delicious'));
        $this->assertFalse(IPUtils::isIpRange('10.66.23.123/eating/potatoes/32'));
        $this->assertFalse(IPUtils::isIpRange('10.66.23.123/32/eating/potatoes'));
    }
}
