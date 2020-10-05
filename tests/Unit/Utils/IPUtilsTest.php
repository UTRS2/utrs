<?php

namespace Tests\Unit\Utils;

use App\Utils\IPUtils;
use PHPUnit\Framework\TestCase;

class IPUtilsTest extends TestCase
{
    public function test_detects_ipv4_addresses()
    {
        $this->assertTrue(IPUtils::isIp('1.2.3.4'));
        $this->assertTrue(IPUtils::isIp('100.200.1.1'));
        $this->assertTrue(IPUtils::isIp('255.255.255.255'));
    }

    public function test_detects_ipv6_addresses()
    {
        $this->assertTrue(IPUtils::isIp('2606:4700:4700::1111'));
        $this->assertTrue(IPUtils::isIp('2400:cb00:2048:1::c629:d7a2'));
    }

    public function test_does_not_detect_malformed_ipv4_addresses()
    {
        $this->assertFalse(IPUtils::isIp('1.1.1.256'));
        $this->assertFalse(IPUtils::isIp('1.1.1'));
        $this->assertFalse(IPUtils::isIp('1.1.1.1.1'));
    }

    public function test_does_not_detect_ipv4_ranges_as_addresses()
    {
        $this->assertFalse(IPUtils::isIp('10.1.1.0/24'));
        $this->assertFalse(IPUtils::isIp('10.20.16.0/21'));
        $this->assertFalse(IPUtils::isIp('10.66.23.123/32'));
    }

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

    public function test_range_normalize()
    {
        $this->assertEquals('1.2.3.0/24', IPUtils::normalizeRange('1.2.3.4/24'));
        $this->assertEquals('1.2.3.0/24', IPUtils::normalizeRange('001.2.3.004/24'));
        $this->assertEquals('1.2.192.0/20', IPUtils::normalizeRange('1.2.200.4/20'));
        $this->assertEquals('2001:DB8:85A3:1234:0:0:0:0/64', IPUtils::normalizeRange('2001:0db8:85a3:1234:0000:8a2e:0370:7334/64'));
    }

    public function test_cut_range_part()
    {
        $this->assertEquals('1.2.3.4', IPUtils::cutCidrRangePart('1.2.3.4/24'));
        $this->assertEquals('1.2.3.5', IPUtils::cutCidrRangePart('1.2.3.5'));
    }

    public function test_get_all_parent_ranges_ipv4()
    {
        $this->assertEquals(24, sizeof(IPUtils::getAllParentRanges('1.2.3.4/24')));

        $result = IPUtils::getAllParentRanges('1.2.3.4');
        $this->assertEquals(IPUtils::MAX_RANGE_SIZE_V4, sizeof($result));
        $this->assertContains('1.2.3.4/32', $result);
        $this->assertContains('1.2.3.0/24', $result);
    }

    public function test_get_all_parent_ranges_ipv6()
    {
        $result = IPUtils::getAllParentRanges('2001:0db8:85a3:1234:0000:8a2e:0370:7334/64');
        $this->assertEquals(64, sizeof($result));
        $this->assertContains('2001:DB8:85A3:1234:0:0:0:0/64', $result);
        $this->assertContains('2001:DB8:85A3:1200:0:0:0:0/56', $result);
        $this->assertContains('2001:DB8:0:0:0:0:0:0/32', $result);
    }
}
