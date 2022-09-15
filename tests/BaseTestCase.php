<?php
declare(strict_types=1);

namespace acoby;

use PHPUnit\Framework\TestCase;
use acoby\system\BodyMapper;

abstract class BaseTestCase extends TestCase {
  protected $mapper;

  public function setUp() :void {
    $this->mapper = new BodyMapper();
  }
}
