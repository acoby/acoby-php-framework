<?php
declare(strict_types=1);

namespace acoby\services;

use RuntimeException;
use acoby\models\History;
use acoby\exceptions\IllegalArgumentException;
use acoby\system\DatabaseMapper;

class HistoryFactory extends AbstractFactory {
  /** @var HistoryFactory */
  private static $instance = null;
  
  /**
   * Returns the singleton instance of this factory
   * 
   * @return HistoryFactory
   */
  public static function getInstance() :HistoryFactory {
    if (self::$instance === null) self::$instance = new HistoryFactory();
    return self::$instance;
  }
  
  /**
   * Creates a new object in the database
   *
   * @param History $history
   * @return History|NULL
   */
  public function createHistory(History $history, string $creatorId = null) :?History {
    $history->creatorId = $creatorId;
    
    if (!$history->verify(true)) throw new IllegalArgumentException("History definition is not valid");
    
    $stmt = DatabaseMapper::getInstance()->insert($this->connection,History::TABLE_NAME, $history);
    if ($stmt === null) throw new RuntimeException("Could not store history in database");
    
    return $this->geHistoryByExternalId($history->externalId);
  }
  
  /**
   * Returns the history object with given externalId
   *
   * @param string $networkId
   * @param string $ownerId
   * @return History|NULL
   */
  public function geHistoryByExternalId(string $externalId, bool $expand = false) :?History {
    $params = array();
    $params["externalId"] = $externalId;
    $condition = "`externalId`=:externalId";
    
    return DatabaseMapper::getInstance()->findOne($this->connection, History::TABLE_NAME, History::class, $expand, $condition, $params, null);
  }
  
  
  /**
   * Returns a list of all history changes to a given objective.
   * 
   * @param string $objectType
   * @param string $objectId
   * @param bool $expand
   * @param int $offset
   * @param int $limit
   * @param bool $orderAscending order of history
   * @return History[]
   */
  public function getHistoryByObject(string $objectType, string $objectId, bool $expand = false, int $offset = 0, int $limit = 100, bool $orderAscending = false) :array {
    $order = "DESC";
    if ($orderAscending) $order = "ASC";
    
    $params = array();
    $params['objectType'] = $objectType;
    $params['objectId'] = $objectId;
    $condition = "`objectType` = :objectType AND `objectId` = :objectId ORDER BY `created` ".$order;

    return DatabaseMapper::getInstance()->findAll($this->connection, History::TABLE_NAME, History::class, $condition, $params, $expand, $limit, $offset);
  }
  
  
  /**
   * Returns a list of changes a User did
   * 
   * @param string $creatorId the creator who changes something
   * @param bool $expand to expand all History objects
   * @param int $offset offset of data
   * @param int $limit limit of data
   * @param bool $orderAscending order of history
   * @return History[]
   */
  public function getHistoryByCreator(string $creatorId, bool $expand = false, int $offset = 0, int $limit = 100, bool $orderAscending = false) :array {
    $order = "DESC";
    if ($orderAscending) $order = "ASC";
    
    $params = array();
    $params['creatorId'] = $creatorId;
    $conditions = "`creatorId`=:creatorId ORDER BY `created` ".$order;
    
    return DatabaseMapper::getInstance()->findAll($this->connection, History::TABLE_NAME, History::class, $conditions, $params, $expand, $limit, $offset);
  }
}