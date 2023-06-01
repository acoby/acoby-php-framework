<?php
declare(strict_types=1);

namespace acoby\system;

use acoby\BaseTestCase;
use Exception;

class FunctionsTest extends BaseTestCase {
  /**
   * @throws Exception
   */
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

  /**
   * @throws Exception
   */
  public function testCurve() {
    $curve = new Curve25519();
    $privateKey1 = base64_encode(random_bytes(32));
    $publicKey1 = base64_encode($curve->publicKey(base64_decode($privateKey1)));
    
    $this->assertNotNull($publicKey1);
    
    $privateKey2 = random_bytes(32);
    $publicKey2 = $curve->publicKey($privateKey2);
    
    $sharedKey = $curve->sharedKey(base64_decode($privateKey1), $publicKey2);
    
    $this->assertNotNull($sharedKey);
    
  }
}
