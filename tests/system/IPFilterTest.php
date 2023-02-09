<?php
declare(strict_types=1);

namespace acoby\system;

use Exception;
use acoby\BaseTestCase;

class IPFilterTest extends BaseTestCase {
  public function testWrongRange() {
    $filter = new IPFilter();
    $this->assertNull($filter->first());
    $this->assertFalse($filter->inrange('10.0.1.0'));
  }

  public function testInRangeIPv4Mask() {
    $array = array("10.0.0.0/24");
    $filter = new IPFilter($array);

    $this->assertTrue($filter->inrange('10.0.0.1'));
    $this->assertTrue($filter->inrange('10.0.0.255'));
    $this->assertFalse($filter->inrange('10.0.1.0'));
    $this->assertFalse($filter->inrange('fd00:1::1'));
  }

  public function testInRangeIPv4Single() {
    $array = array("10.0.0.0");
    $filter = new IPFilter($array);
    $this->assertTrue($filter->inrange('10.0.0.0'));
    $this->assertFalse($filter->inrange('10.0.0.1'));
    $this->assertFalse($filter->inrange('fd00:1::1'));
  }

  public function testInRangeIPv4Section() {
    $array = array("10.0.0.0-10.0.0.255");
    $filter = new IPFilter($array);
    $this->assertTrue($filter->inrange('10.0.0.1'));
    $this->assertTrue($filter->inrange('10.0.0.250'));
    $this->assertFalse($filter->inrange('10.0.1.0'));
    $this->assertFalse($filter->inrange('fd00:1::1'));
    $this->assertFalse($filter->inrange('null'));
  }

  public function testInRangeIPv4Wildcard() {
    $array = array("10.0.0.*");
    $filter = new IPFilter($array);
    $this->assertTrue($filter->inrange('10.0.0.1'));
    $this->assertTrue($filter->inrange('10.0.0.250'));
    $this->assertFalse($filter->inrange('10.0.1.0'));
    $this->assertFalse($filter->inrange('fd00:1::1'));
    $this->assertFalse($filter->inrange('null'));
  }

  public function testInRangeIPv6Mask() {
    $array = array("fd00:1::/64");
    $filter = new IPFilter($array);

    $this->assertTrue($filter->inrange('fd00:1::1'));
    $this->assertTrue($filter->inrange('fd00:1::ffff:ffff:ffff:ffff'));
    $this->assertFalse($filter->inrange('fd00:1:1::1'));
    $this->assertFalse($filter->inrange('10.0.0.0'));
  }

  public function testInRangeIPv6Single() {
    $array = array("fd00:1::0");
    $filter = new IPFilter($array);

    $this->assertTrue($filter->inrange('fd00:1::0'));
    $this->assertFalse($filter->inrange('fd00:1::1'));
    $this->assertFalse($filter->inrange('10.0.0.1'));
  }

  public function testInRangeIPv6Section() {
    $array = array("fd00:1::-fd00:1::ffff");
    $filter = new IPFilter($array);
    $this->expectException(Exception::class);
    $this->assertFalse($filter->inrange('fd00:1::1'));
  }

  public function testInRangeIPv6Wildcard() {
    $array = array("fd00:1:*::");
    $filter = new IPFilter($array);
    $this->expectException(Exception::class);
    $this->assertFalse($filter->inrange('fd00:1::1'));
  }

  public function testFirstIPv4() {
    $array = array("10.0.0.0/24");
    $filter = new IPFilter($array);
    $this->assertEquals('10.0.0.0',$filter->first());
    $this->assertEquals('10.0.0.0',$filter->firstIPv4());
    $this->assertNull($filter->firstIPv6());
    $this->assertTrue($filter->inrange($filter->first()));

    $array = array("10.0.*");
    $filter = new IPFilter($array);
    $this->assertEquals('10.0.0.0',$filter->firstIPv4());
    $this->assertEquals('10.0.0.0',$filter->first());

    $array = array("10.0.0.0-10.0.0.255");
    $filter = new IPFilter($array);
    $this->assertEquals('10.0.0.0',$filter->firstIPv4());
    $this->assertEquals('10.0.0.0',$filter->first());
    $this->assertNotEquals('10.0.0.255',$filter->first());

    $filter = new IPFilter();
    $this->assertEquals('10.0.0.0',$filter->first("10.0.0.0/24"));
    $this->assertEquals('10.0.0.0',$filter->firstIPv4("10.0.0.0/24"));
    $this->assertEquals('10.0.1.0',$filter->firstIPv4("10.0.1.*"));
  }

  public function testLastIPv4() {
    $array = array("10.0.0.0/24");
    $filter = new IPFilter($array);
    $this->assertEquals('10.0.0.255',$filter->last());
    $this->assertEquals('10.0.0.255',$filter->lastIPv4());

    $array = array("10.0.*");
    $filter = new IPFilter($array);
    $this->assertEquals('10.0.255.255',$filter->last());
    $this->assertEquals('10.0.255.255',$filter->lastIPv4());

    $array = array("10.0.0.0-10.0.0.255");
    $filter = new IPFilter($array);
    $this->assertEquals('10.0.0.255',$filter->last());
    $this->assertEquals('10.0.0.255',$filter->lastIPv4());

    $filter = new IPFilter();
    $this->assertEquals('10.0.1.255',$filter->last("10.0.1.0/24"));
    $this->assertEquals('10.0.1.255',$filter->lastIPv4("10.0.1.0/24"));
    $this->assertEquals('10.0.1.255',$filter->lastIPv4("10.0.1.*"));
    $this->assertEquals('10.0.1.255',$filter->lastIPv4("10.0.1.0-10.0.1.255"));
    $this->assertNull($filter->last("null"));
    $this->assertNull($filter->lastIPv4("null"));
  }

  public function testLastIPv6Mask() {
    $array = array("fd00:1::/64");
    $filter = new IPFilter($array);
    $this->assertEquals('fd00:1::ffff:ffff:ffff:ffff',$filter->last());
    $this->assertEquals('fd00:1::ffff:ffff:ffff:ffff',$filter->lastIPv6());
  }

  public function testLastIPv6Wildcard() {
    $array = array("fd00:1:*");
    $filter = new IPFilter($array);
    $this->expectException(Exception::class);
    $this->assertEquals('fd00:1:ffff:ffff:ffff:ffff:ffff:ffff',$filter->last());
    $this->assertEquals('fd00:1:ffff:ffff:ffff:ffff:ffff:ffff',$filter->lastIPv6());
  }

  public function testLastIPv6Section() {
    $array = array("fd00:1::0-fd00:1::ffff");
    $filter = new IPFilter($array);
    $this->assertEquals('fd00:1::ffff',$filter->last());
    $this->assertEquals('fd00:1::ffff',$filter->lastIPv6());
  }

  public function testLastIPv6Simple() {
    $filter = new IPFilter();
    $this->assertEquals('fd00:1::ffff:ffff:ffff:ffff',$filter->last("fd00:1::/64"));
    $this->assertEquals('fd00:1::ffff:ffff:ffff:ffff',$filter->lastIPv6("fd00:1::/64"));
    $this->assertEquals('fd00:1::ffff',$filter->lastIPv6("fd00:1::0-fd00:1::ffff"));
    $this->assertNull($filter->last("null"));
    $this->assertNull($filter->lastIPv6("null"));
  }

  public function testFirstIPv6() {
    $array = array("fd00:1::/64");
    $filter = new IPFilter($array);
    $this->assertEquals('fd00:1::0',$filter->first());
    $this->assertEquals('fd00:1::0',$filter->firstIPv6());
    $this->assertNull($filter->firstIPv4());
    $this->assertTrue($filter->inrange($filter->first()));

    $array = array("fd00:1:*");
    $filter = new IPFilter($array);
    $this->assertEquals('fd00:1::0',$filter->firstIPv6());
    $this->assertEquals('fd00:1::0',$filter->first());

    $array = array("fd00:1::0-fd00:1::ffff");
    $filter = new IPFilter($array);
    $this->assertEquals('fd00:1::0',$filter->firstIPv6());
    $this->assertEquals('fd00:1::0',$filter->first());

    $filter = new IPFilter();
    $this->assertEquals('fd00:1::0',$filter->first("fd00:1::/64"));
    $this->assertEquals('fd00:1::0',$filter->firstIPv6("fd00:1::/64"));
  }

  public function testFirstIPMix() {
    $array = array("10.0.0.0/24","fd00:1::/64");
    $filter = new IPFilter($array);
    $this->assertEquals('10.0.0.0',$filter->firstIPv4());
    $this->assertEquals('fd00:1::0',$filter->firstIPv6());
    $this->assertNotNull($filter->first());
  }

  public function testIncrementIPv4() {
    $array = array("10.0.0.0/24");
    $filter = new IPFilter($array);
    $first = $filter->first();
    $next = $filter->increment($first, 100);
    $this->assertEquals('10.0.0.100',$next);
  }

  public function testIncrementIPv6() {
    $array = array("fd00:1::/64");
    $filter = new IPFilter($array);
    $first = $filter->first();
    $next = $filter->increment($first, 100);
    $this->assertEquals('fd00:1::64',$next);
  }
  public function testIncrementWrongString() {
    $filter = new IPFilter();
    $this->assertNull($filter->increment("null", 1));
  }

  public function testExpandIPv6() {
    $filter = new IPFilter();
    $ip = $filter->expandIPv6("fd00:1::10");
    $this->assertEquals('fd00:0001:0000:0000:0000:0000:0000:0010',$ip);
  }

  public function testisIP() {
    $filter = new IPFilter();
    $this->assertTrue($filter->isIP("10.10.10.10"));
    $this->assertTrue($filter->isIP("fd00:0:0:0:1::"));
    $this->assertFalse($filter->isIP("256.0.0.0"));
    $this->assertFalse($filter->isIP("nix:null"));
  }

  public function testPackingUnpackingIPv6() {
    $filter = new IPFilter();
    $text = "fd00:1::f000";
    $data = $filter->dtr_pton($text);

    $this->assertNotNull($data);
    $ip = $filter->dtr_ntop($data);

    $this->assertEquals($text,$ip);
  }

  public function testPackingUnpackingIPv4() {
    $filter = new IPFilter();
    $text = "10.0.0.1";
    $data = $filter->dtr_pton($text);

    $this->assertNotNull($data);
    $ip = $filter->dtr_ntop($data);

    $this->assertEquals($text,$ip);
  }

  public function testPackingWrongIp() {
    $filter = new IPFilter();
    $text = "null";
    $this->expectException(Exception::class);
    $filter->dtr_pton($text);
  }

  public function testUnpackingWrongIp() {
    $filter = new IPFilter();
    $text = "nil";
    $this->expectException(Exception::class);
    $filter->dtr_ntop($text);
  }

  public function testGetNetwork() {
    $filter = new IPFilter();
    $this->assertEquals("10.0.0.0",$filter->getNetwork("10.0.0.0/24"));
    $this->assertEquals("fd00:1::",$filter->getNetwork("fd00:1::/64"));

    $this->assertNull($filter->getNetwork("null"));
    $this->assertNull($filter->getNetwork("10.0.0.0"));
    $this->assertNull($filter->getNetwork("fd00:1::"));
  }

  public function testGetMaskIPv4() {
    $filter = new IPFilter();

    $this->assertEquals("10.0.0.0",$filter->getIPv4Wildcard("10.0.*"));
    $this->assertNull($filter->getIPv4Wildcard("null"));
    $this->assertNull($filter->getIPv4Wildcard("10.0.0.0"));
    $this->assertNull($filter->getIPv4Wildcard("fd00:1::"));
  }

  public function testGetMaskIPv6() {
    $filter = new IPFilter();

    $this->assertEquals("fd00:1::",$filter->getIPv6Wildcard("fd00:1:*"));
    $this->assertNull($filter->getIPv6Wildcard("null"));
    $this->assertNull($filter->getIPv6Wildcard("10.0.0.0"));
    $this->assertNull($filter->getIPv6Wildcard("fd00:1::"));
  }

  public function testGetFirstSectionIPv4() {
    $filter = new IPFilter();

    $this->assertEquals("10.0.0.0",$filter->getIPv4Section("10.0.0.0-10.0.0.255"));
    $this->assertNotEquals("10.0.0.1",$filter->getIPv4Section("10.0.0.0-10.0.0.255"));
    $this->assertNull($filter->getIPv4Section("null"));
    $this->assertNull($filter->getIPv4Section("10.0.0.0"));
    $this->assertNull($filter->getIPv4Section("fd00:1::"));
  }

  public function testGetFirstSectionIPv6() {
    $filter = new IPFilter();

    $this->assertEquals("fd00:1::0",$filter->getIPv6Section("fd00:1::0-fd00:1::ffff"));
    $this->assertNotEquals("fd00:1::1",$filter->getIPv6Section("fd00:1::0-fd00:1::ffff"));
    $this->assertNull($filter->getIPv6Section("null"));
    $this->assertNull($filter->getIPv6Section("10.0.0.0"));
    $this->assertNull($filter->getIPv6Section("fd00:1::"));
  }}
