<?php
declare(strict_types=1);

namespace acoby\services;

use acoby\models\AbstractUser;

abstract class UserFactory extends AbstractFactory {
  const ADMIN = "ADMIN";
  const MANAGER = "MANAGER";
  const USER = "USER";
  const REPORT = "REPORT";
  
  /**
   * Returns the singleton of this class 
   */
  public static abstract function getInstance() :UserFactory;
  
  public static abstract function hasRole(AbstractUser $user, string $role): bool;
}