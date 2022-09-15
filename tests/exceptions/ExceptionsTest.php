<?php
declare(strict_types=1);

namespace acoby\exceptions;

use acoby\BaseTestCase;

class ExceptionsTest extends BaseTestCase {
  public function testIllegalStateException() {
    $e = new IllegalStateException("test");
    $this->assertEquals("test", $e->getMessage());
  }
  public function testMissingAttributeException() {
    $e = new MissingAttributeException("test");
    $this->assertEquals("test", $e->getMessage());
  }
  public function testNotSupportedException() {
    $e = new NotSupportedException("test");
    $this->assertEquals("test", $e->getMessage());
  }
}
