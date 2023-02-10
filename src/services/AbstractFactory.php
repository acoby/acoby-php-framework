<?php
declare(strict_types=1);

namespace acoby\services;

use PDO;
use acoby\system\DatabaseMapper;

abstract class AbstractFactory {
  protected $connection;

  /** */
  protected function __construct() {
    global $pdo;
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
}