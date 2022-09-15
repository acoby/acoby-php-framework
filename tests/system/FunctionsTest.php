<?php
declare(strict_types=1);

namespace acoby\system;

use acoby\BaseTestCase;

class FunctionsTest extends BaseTestCase {
  public function testRandomStr() {
    $str = Utils::random_str(10);
    $this->assertIsString($str);
    $this->assertEquals(10, strlen($str));
  }

  public function testStart() {
    $message = "hallo";
    $val = Utils::startsWith($message, "ha");
    $this->assertTrue($val);

    $val = Utils::startsWith($message, "al");
    $this->assertFalse($val);

    $val = Utils::startsWith($message, "");
    $this->assertTrue($val);
  }

  public function testEnd() {
    $message = "hallo";
    $val = Utils::endsWith($message, "lo");
    $this->assertTrue($val);

    $val = Utils::endsWith($message, "hal");
    $this->assertFalse($val);

    $val = Utils::endsWith($message, "");
    $this->assertTrue($val);
  }
}
