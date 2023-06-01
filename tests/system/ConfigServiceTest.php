<?php
declare(strict_types=1);

namespace acoby\system;

use acoby\BaseTestCase;
use acoby\services\ConfigService;

class ConfigServiceTest extends BaseTestCase {
  public function testAll() {
    $value1 = ConfigService::getString("testkey");
    $this->assertNull($value1);

    $value2 = ConfigService::getString("testkey","test");
    $this->assertNotNull($value2);
    $this->assertEquals("test", $value2);

    $value3 = ConfigService::getInt("testkey");
    $this->assertEquals(0, $value3);

    $value4 = ConfigService::getInt("testkey",1);
    $this->assertNotNull($value4);
    $this->assertEquals(1, $value4);

    $value5 = ConfigService::getBool("testkey");
    $this->assertNull($value5);

    $value6 = ConfigService::getBool("testkey",true);
    $this->assertNotNull($value6);
    $this->assertTrue($value6);
  }

  public function testSet() {
    ConfigService::unset("setKey");

    $contains1 = ConfigService::contains("setKey");
    ConfigService::set("setKey","value");
    $value = ConfigService::get("setKey");
    $this->assertEquals("value", $value);

    $contains2 = ConfigService::contains("setKey");
    $this->assertNotEquals($contains2, $contains1);

    ConfigService::unset("setKey");
    $value = ConfigService::get("setKey");
    $this->assertNull($value);
  }

  public function testArray() {
    $value = ConfigService::getArray("testarray");
    $this->assertNull($value);

    ConfigService::setArray("testarray",["value1"]);
    $value = ConfigService::getArray("testarray");
    $this->assertIsArray($value);
  }
}
