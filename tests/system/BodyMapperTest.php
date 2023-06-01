<?php
declare(strict_types=1);

namespace acoby\system;

use acoby\BaseTestCase;
use acoby\exceptions\IllegalArgumentException;

class BodyMapperTest extends BaseTestCase {
  public function testFailure1() {
    $mapper = new BodyMapper();
    $this->expectException(IllegalArgumentException::class);
    $mapper->map('{"wrong":"wrong",}', new BodyMapper());
  }

  public function testFailure2() {
    $mapper = new BodyMapper();
    $this->expectException(IllegalArgumentException::class);
    $mapper->decode('{"wrong":"wrong",}');
  }

  /**
   * @throws IllegalArgumentException
   */
  public function testSuccess() {
    $mapper = new BodyMapper();
    $array = $mapper->decode('{"correct":"correct"}');
    $this->assertIsArray($array);
    $this->assertCount(1, $array);
  }
}