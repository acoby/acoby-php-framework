<?php
declare(strict_types=1);

namespace acoby\services;

use acoby\models\AbstractUser;

/**
 * A custom UserFactory should implement this interface and should be registered
 * to the AbstractFactory with:
 * 
 * AbstractFactory::setUserService();
 * 
 * @author Thoralf Rickert-Wendt
 */
interface UserService {
  const ADMIN = "ADMIN";
  const MANAGER = "MANAGER";
  const USER = "USER";
  const REPORT = "REPORT";

  /**
   * Checks if the given user has a given role.
   * 
   * @param AbstractUser $user
   * @param string $role
   * @return bool
   */
  public static function hasRole(AbstractUser $user, string $role): bool;

  /**
   * Returns the user with the given username
   * 
   * @param string $username
   * @param bool $expand
   * @return AbstractUser|NULL
   */
  public function getUserByName(string $username, bool $expand = true) :?AbstractUser;
}