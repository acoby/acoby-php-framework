<?php
declare(strict_types=1);

namespace acoby\services;

use PDO;
use acoby\system\DatabaseMapper;
use acoby\exceptions\IllegalStateException;

/**
 * A Base Factory for all you needs
 * 
 * @author Thoralf Rickert-Wendt
 */
abstract class AbstractFactory {
  protected $connection;
  private static $services = array();

  /** */
  protected function __construct() {
    global $pdo;
    $this->connection = $pdo;
  }
  
  /**
   * Sets the DB connection to this factory
   * 
   * @param PDO $pdo
   */
  public function setConnection(PDO $pdo) :void {
    $this->connection = $pdo;
  }

  /**
   * Returns the number of entries in the given table
   * 
   * @param string $table the table name to count
   * @param bool $ignoreDeleted true when ignoring deleted fields
   * @return number
   */
  public function getObjectCount(string $table, bool $ignoreDeleted = true) :int {
    return DatabaseMapper::getInstance()->getObjectCount($this->connection, $table, $ignoreDeleted);
  }

  /**
   * Checks if in the given table a row exists that contains a specific value in a specific column
   *
   * @param string $table the table to search for
   * @param string $column the column in the table to search for
   * @param string $value the value in the column in the table to search for
   * @param string|null $externalId ignore rows with that id (to avoid duplicates)
   * @return bool true, when there is a row with that value in the given column in the given table
   */
  public function existsObject(string $table, string $column, string $value, string $externalId = null) :bool {
    return DatabaseMapper::getInstance()->existsObject($this->connection, $table, $column, $value, $externalId);
  }

  /**
   * Sets a specific instance of service
   *
   * @param string $key
   * @param $service
   */
  public static function setService(string $key, $service) :void {
    AbstractFactory::$services[$key] = $service;
  }
  
  /**
   * Returns the Singleton of a service.
   *
   * @throws IllegalStateException
   * @return UserService
   */
  public static function getService(string $key) :object {
    if (!isset(AbstractFactory::$services[$key])) throw new IllegalStateException("'".$key."' is not registered");
    return AbstractFactory::$services[$key];
  }
  /**
   * Sets the Singleton of UserService.
   * 
   * @param UserService $userService
   */
  public static function setUserService(UserService $userService) :void {
    AbstractFactory::setService("UserService",$userService);
  }
  
  /**
   * Returns the Singleton of UserService.
   * 
   * @throws IllegalStateException
   * @return UserService
   */
  public static function getUserService() :UserService {
    return AbstractFactory::getService("UserService");
  }
}