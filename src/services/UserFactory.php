<?php
declare(strict_types=1);

namespace acoby\services;

class UserFactory {
  protected static $instance = null;
  
  const ADMIN = "ADMIN";
  const MANAGER = "MANAGER";
  const USER = "USER";
  const REPORT = "REPORT";
  
  /** */
  public static function getInstance() :UserFactory {
    if (self::$instance === null) self::$instance = new UserFactory();
    return self::$instance;
  }
  
}