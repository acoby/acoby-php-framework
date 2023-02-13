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
    $query = "SELECT count(id) FROM `".$table."`";
    if ($ignoreDeleted) $query.= " WHERE `deleted` IS NULL";
    $params = array();
    $counts = DatabaseMapper::getInstance()->query($this->connection, $query, $params, PDO::FETCH_COLUMN, 0);
    if ($counts === null || count($counts)<1) return 0;
    return $counts[0];
  }
  
  /**
   * Checks if in the given table a row exists that contains a specific value in a specific column
   * 
   * @param string $table the table to search for
   * @param string $column the column in the table to search for
   * @param string $value the value in the column in the table to search for
   * @param string $externalId ignore rows with that id (to avoiid duplicates)
   * @return bool true, when there is a row with that value in the given column in the given table
   */
  public function existsObject(string $table, string $column, string $value, string $externalId = null) :bool {
    $params = array();
    $params["value"] = $value;
    $query = "SELECT count(id) FROM `".$table."` WHERE `".$column."` = :value";
    if ($externalId !== null) {
      $query .= " AND externalId <> :externalId";
      $params["externalId"] = $externalId;
    }
    
    $counts = DatabaseMapper::getInstance()->query($this->connection, $query, $params, \PDO::FETCH_COLUMN, 0);
    if ($counts === null || count($counts)<1) return false;
    return $counts[0] > 0;
  }
  
  /**
   * Sets a specific instance of service
   * 
   * @param string $key
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