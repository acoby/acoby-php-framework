<?php
declare(strict_types=1);

namespace acoby\system;

use PDO;
use PDOStatement;
use acoby\exceptions\DatabaseException;
use Exception;
use acoby\models\AbstractSearch;
use acoby\services\ConfigService;

class DatabaseMapper {
  private static $instance = null;

  public static function getInstance() :DatabaseMapper {
    if (self::$instance === null) self::$instance = new DatabaseMapper();
    return self::$instance;
  }

  /**
   * Returns an array with all columns of the given table
   *
   * @param PDO $connection
   * @param string $tableName
   * @throws DatabaseException
   * @return string[]
   */
  private function getColumns(PDO $connection, string $tableName) {
    if (!isset($connection)) throw new DatabaseException("PDO not initialized");

    $query = "SELECT * FROM `".$tableName."` LIMIT 0";
    $result = $connection->query($query);
    if ($result === false) throw new DatabaseException("Table ".$tableName." is not available");
    $columns = array();
    for ($i = 0; $i < $result->columnCount(); $i++) {
      $col = $result->getColumnMeta($i);
      $columns[] = $col['name'];
    }
    return $columns;
  }

  /**
   * Fügt an den Insert String $query für den Schlüssel in $key den Wert in $var an.
   *
   * @param string $query
   * @param array $params
   * @param mixed $var
   * @param string $key
   */
  private function addInsertParam(array &$query, array &$params, &$var, string $key) :void {
    if (!isset($var)) return;
    if (!Utils::endsWith($query[0], "(")) $query[0] .= ",";
    $query[0] .= "`".$key."`";

    if (!Utils::endsWith($query[1], "(")) $query[1] .= ",";
    $query[1] .= ":".$key;
    $params[":".$key] = $var;
  }
  /**
   *
   * @param PDO $connection
   * @param string $query
   * @param array $params
   * @return PDOStatement|NULL
   */
  public function insert(PDO $connection, string $tableName, object $object) :?PDOStatement {
    if (!isset($connection)) throw new DatabaseException("PDO not initialized");
    
    $tableColumns = $this->getColumns($connection, $tableName);
    $classFields = array_keys(get_class_vars(get_class($object)));

    $params = array();
    $query = array();
    $query[0] = 'INSERT INTO `'.$tableName.'` (';
    $query[1] = ') VALUES (';
    $query[2] = ')';

    foreach ($classFields as $field) {
      if (in_array($field,$tableColumns)) {
        $this->addInsertParam($query, $params, $object->$field, $field);
      } else {
        if (isset($object->$field)) {
          //error_log("WARN: the class field: ".get_class($object)."->".$field." (".gettype($object->$field).") has an value, but it has no pendant in table ".$tableName);
        }
      }
    }

    $q = $query[0].$query[1].$query[2];

    $stmt = $connection->prepare($q);
    if (!$stmt) {
      // @codeCoverageIgnoreStart
      Utils::logError("Failed to parse query: ".$q." ".print_r($params,true));
      throw new DatabaseException("Failed to parse query: ".$q." ".print_r($params,true));
      // @codeCoverageIgnoreEnd
    }
    foreach ($params as $key => &$value) {
      $stmt->bindParam($key, $value);
    }

    try {
      if (!$stmt->execute()) {
        // @codeCoverageIgnoreStart
        Utils::logError("Failed query",$q,$params,$stmt->errorInfo());
        return null;
        // @codeCoverageIgnoreEnd
      }
      return $stmt;
      // @codeCoverageIgnoreStart
    } catch (Exception $e) {
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $stmt->errorInfo());
    }
    return null;
    // @codeCoverageIgnoreEnd
  }

  /**
   * Fügt an den Update String $query für den Schlüssel in $key den Wert in $var an.
   *
   * @param string $query
   * @param array $params
   * @param mixed $var
   * @param string $key
   */
  public function addUpdateParam(string &$query, array &$params, &$var, string $key) :void {
    if (!isset($var)) return;
    if (!Utils::endsWith($query, ' SET ')) $query .=" ,";
    $query .= "`".$key."` = :".$key." ";
    $params[":".$key] = $var;
  }

  /**
   * Fügt an den String $query für den Schlüssel in $key den Wert in $var an.
   *
   * @param string $query
   * @param array $params
   * @param mixed $var
   * @param string $key
   */
  private function addToQuery(string $key, string $operator, bool $orNull) :string {
    if ($orNull) {
      return " (`".$key."` ".$operator." :".$key." OR `".$key."` IS NULL) ";
    } else {
      return " `".$key."` ".$operator." :".$key." ";
    }
  }

  /**
   * Fügt an den String $query für den Schlüssel $key den Wert in $var an.
   *
   * @param string $query
   * @param array $params
   * @param mixed $var
   * @param string $key
   * @param string $type
   */
  public function addSelectParam(string &$query, array &$params, &$var, string $key, string $type="string", bool $orNull = false) :void {
    if (!isset($var)) return;
    if ($type == "string") {
      if (strpos($var,"*")===false) {
        $query .= " AND ".$this->addToQuery($key,"=",$orNull);
        $params[":".$key] = $var;
      } else {
        $query .= " AND ".$this->addToQuery($key,"LIKE",$orNull);
        $params[":".$key] = str_replace("*","%",$var);
      }
    } else  if ($type == "integer") {
      if (strpos($var,">=")===0) {
        $query .= " AND ".$this->addToQuery($key,">=",$orNull);
        $params[":".$key] = str_replace(">=","",$var);
      } else if (strpos($var,">")===0) {
        $query .= " AND ".$this->addToQuery($key,">",$orNull);
        $params[":".$key] = str_replace(">","",$var);
      } else if (strpos($var,"<=")===0) {
        $query .= " AND ".$this->addToQuery($key,"<=",$orNull);
        $params[":".$key] = str_replace("<=","",$var);
      } else if (strpos($var,"<")===0) {
        $query .= " AND ".$this->addToQuery($key,"<",$orNull);
        $params[":".$key] = str_replace("<","",$var);
      } else {
        $query .= " AND ".$this->addToQuery($key,"=",$orNull);
        $params[":".$key] = $var;
      }
    } else if ($type == "datetime") {
      if (strpos($var,">=")===0) {
        $query .= " AND ".$this->addToQuery($key,">=",$orNull);
        $params[":".$key] = str_replace(">=","",$var);
      } else if (strpos($var,">")===0) {
        $query .= " AND ".$this->addToQuery($key,">",$orNull);
        $params[":".$key] = str_replace(">","",$var);
      } else if (strpos($var,"<=")===0) {
        $query .= " AND ".$this->addToQuery($key,"<=",$orNull);
        $params[":".$key] = str_replace("<=","",$var);
      } else if (strpos($var,"<")===0) {
        $query .= " AND ".$this->addToQuery($key,"<",$orNull);
        $params[":".$key] = str_replace("<","",$var);
      } else {
        $query .= " AND ".$this->addToQuery($key,"=",$orNull);
        $params[":".$key] = $var;
      }
    }
  }

  /**
   *
   * @param PDO $connection
   * @param string $tableName
   * @param object $object
   * @param string $identifierName
   * @param string $identifier
   * @throws DatabaseException
   * @return bool
   */
  public function update(PDO $connection, string $tableName, object $object, string $identifierName, string $identifier) :bool {
    if (!isset($connection)) throw new DatabaseException("PDO not initialized");
    
    $tableColumns = $this->getColumns($connection, $tableName);
    $classFields = array_keys(get_class_vars(get_class($object)));

    $params = array();
    $query = 'UPDATE `'.$tableName.'` SET ';
    foreach ($classFields as $field) {
      if (in_array($field,$tableColumns)) {
        $this->addUpdateParam($query, $params, $object->$field, $field);
      }
    }

    $query .= ' WHERE 1=1 ';
    $this->addSelectParam($query, $params, $identifier, $identifierName);

    $stmt = $connection->prepare($query);
    if (!$stmt) {
      // @codeCoverageIgnoreStart
      throw new DatabaseException("Failed to parse query: ".$query);
      // @codeCoverageIgnoreEnd
    }
    foreach ($params as $key => &$value) {
      $stmt->bindParam($key, $value);
    }

    try {
      if (!$stmt->execute()) {
        // @codeCoverageIgnoreStart
        Utils::logError("Unknown error",$query,$params,$stmt->errorInfo());
        return false;
        // @codeCoverageIgnoreEnd
      }
      return true;
      // @codeCoverageIgnoreStart
    } catch (Exception $e) {
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $stmt->errorInfo());
    }
    return false;
    // @codeCoverageIgnoreEnd
  }

  /**
   * Führt eine Funktion aus
   *
   * @codeCoverageIgnore
   * @param PDO $connection
   * @param string $query
   * @param array $params
   * @throws DatabaseException
   * @return bool
   */
  public function exec(PDO $connection, string $query, array &$params) :bool {
    if (!isset($connection)) throw new DatabaseException("PDO not initialized");
    
    $stmt = $connection->prepare($query);
    if (!$stmt) {
      Utils::logError("Failed to parse query: ".$query." ".print_r($params,true));
      throw new DatabaseException("Failed to parse query: ".$query." ".print_r($params,true));
    }
    foreach ($params as $key => &$value) {
      $stmt->bindParam($key, $value);
    }

    try {
      if (!$stmt->execute()) {
        Utils::logError("Query failed", $query, $params, $stmt->errorInfo());
        return false;
      }
      return true;
    } catch (Exception $e) {
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $stmt->errorInfo());
    }
    return false;
  }


  /**
   * Führt eine Funktion aus
   *
   * @param PDO $connection
   * @param string $query
   * @param array $params
   * @throws DatabaseException
   * @return integer
   */
  public function count(PDO $connection, string $query, array &$params) :int {
    if (!isset($connection)) throw new DatabaseException("PDO not initialized");
    
    $stmt = $connection->prepare($query);
    if (!$stmt) {
      Utils::logError("Failed to parse query: ".$query." ".print_r($params,true));
      throw new DatabaseException("Failed to parse query: ".$query." ".print_r($params,true));
    }
    foreach ($params as $key => &$value) {
      $stmt->bindParam($key, $value);
    }

    try {
      if (!$stmt->execute()) {
        Utils::logError("Query failed", $query, $params, $stmt->errorInfo());
        return 0;
      }
      $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
      if ($result[0] == null) return 0;
      return $result[0];
    } catch (Exception $e) {
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $stmt->errorInfo());
    }
    return 0;
  }


  /**
   * Führt eine Funktion aus
   *
   * @param PDO $connection
   * @param string $query
   * @param array $params
   * @throws DatabaseException
   * @return bool
   */
  public function query(PDO $connection, string $query, array &$params, int $fetch_style = PDO::FETCH_NUM, $fetch_arguments = null) :array {
    if (!isset($connection)) throw new DatabaseException("PDO not initialized");
    
    $stmt = $connection->prepare($query);
    if (!$stmt) {
      Utils::logError("Failed to parse query: ".$query." ".print_r($params,true));
      throw new DatabaseException("Failed to parse query: ".$query." ".print_r($params,true));
    }
    foreach ($params as $key => &$value) {
      $stmt->bindParam($key, $value);
    }

    // @codeCoverageIgnoreStart
    try {
      if (!$stmt->execute()) {
        Utils::logError("Query failed", $query, $params, $stmt->errorInfo());
        return [];
      }
      if ($fetch_arguments !== null) {
        return $stmt->fetchAll($fetch_style, $fetch_arguments);
      } else {
        return $stmt->fetchAll($fetch_style);
      }
    } catch (Exception $e) {
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $stmt->errorInfo());
    }
    // @codeCoverageIgnoreEnd
    return [];
  }


  /**
   *
   * @param PDO $connection
   * @param string $tableName
   * @param object $search
   * @return array
   */
  public function search(PDO $connection, string $tableName, AbstractSearch $search, string $resultType, string $orderBy = null) :array {
    if (!isset($connection)) throw new DatabaseException("PDO not initialized");
    
    $tableColumns = $this->getColumns($connection, $tableName);
    $classFields = array_keys(get_class_vars(get_class($search)));
    $searchFields = array_keys(get_class_vars(AbstractSearch::class));

    if (!isset($search->offset)) $search->offset = 0;
    if (!isset($search->limit)) $search->limit = 10;

    $params = array();
    $query = 'SELECT * FROM `'.$tableName.'` WHERE 1=1 ';
//     if ($ownerId !== null) {
//       $query.= 'AND (ownerId = :myOwnerId OR ownerId IN (SELECT customer.externalId FROM customer WHERE customer.ownerId = :myCustomerId)) ';
//       $params["myOwnerId"] = $ownerId;
//       $params["myCustomerId"] = $ownerId;
//     }

    foreach ($classFields as $field) {
      if (!in_array($field, $searchFields)) {
        if (in_array($field,$tableColumns)) {
          $this->addSelectParam($query, $params, $search->$field, $field,gettype($search->$field));
        } else {
          // @codeCoverageIgnoreStart
          Utils::logDebug("INFO: class field: ".$field." has no pendant in table ".$tableName);
          // @codeCoverageIgnoreEnd
        }
      }
    }

    $this->addSelectParam($query, $params, $search->created, "created","datetime");
    $this->addSelectParam($query, $params, $search->changed, "changed","datetime");
    $this->addSelectParam($query, $params, $search->deleted, "deleted","datetime");
    if (!isset($search->deleted)) $query .= " AND deleted is null";

    if (isset($orderBy)) $query.=" ORDER BY `".$orderBy."`";

    if (isset($search->limit) && is_numeric($search->limit)) {
      $query .= " LIMIT ".intval($search->offset).",".intval($search->limit);
    }

    $stmt = $connection->prepare($query);
    if (!$stmt) {
      // @codeCoverageIgnoreStart
      throw new DatabaseException("Failed to parse query: ".$query);
      // @codeCoverageIgnoreEnd
    }

    foreach ($params as $key => &$value) {
      $stmt->bindParam($key, $value);
    }

    try {
      if (!$stmt->execute()) {
        // @codeCoverageIgnoreStart
        Utils::logError("Error in search query",$query,$params,$stmt->errorInfo());
        return null;
        // @codeCoverageIgnoreEnd
      }
      $results = $stmt->fetchAll(PDO::FETCH_CLASS, $resultType);
      foreach($results as $result) $result->reform($search->expand);
      return $results;
      // @codeCoverageIgnoreStart
    } catch (Exception $e) {
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $stmt->errorInfo());
    }
    return null;
    // @codeCoverageIgnoreEnd
  }

  /**
   *
   * @param PDO $connection
   * @param string $tableName
   * @param string $resultType
   * @param string $condition
   * @param bool $expand
   * @param array $params
   * @param number $limit
   * @param number $start
   * @throws DatabaseException
   * @return array
   */
  public function findAll(PDO $connection, string $tableName, string $resultType, string $condition = "", array $params, bool $expand = false, $limit, $offset = 0) :array {
    if (!isset($connection)) throw new DatabaseException("PDO not initialized");
    
    $query = 'SELECT * FROM `'.$tableName.'`';
    if (isset($condition) && strlen($condition) > 0) $query.=" WHERE ".$condition;
    if (isset($limit) && is_numeric($limit)) {
      if ($limit < 1) {
        // @codeCoverageIgnoreStart
        error_log("[WARN] Limit for query is less one, this seems to be wrong");
        $limit = 1;
        // @codeCoverageIgnoreEnd
      }
      $query .= " LIMIT ".intval($offset).",".intval($limit);
    }

    $stmt = $connection->prepare($query);
    if (!$stmt) {
      // @codeCoverageIgnoreStart
      throw new DatabaseException("Failed to parse query: ".$query);
      // @codeCoverageIgnoreEnd
    }

    foreach ($params as $key => &$value) {
      $stmt->bindParam($key, $value);
    }

    try {
      if (!$stmt->execute()) {
        // @codeCoverageIgnoreStart
        Utils::logError("Error in query",$query,$params,$stmt->errorInfo());
        return null;
        // @codeCoverageIgnoreEnd
      }
      $results = $stmt->fetchAll(PDO::FETCH_CLASS, $resultType);
      foreach ($results as $result) $result->reform($expand);
      return $results;
      // @codeCoverageIgnoreStart
    } catch (Exception $e) {
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $stmt->errorInfo());
    }
    return null;
    // @codeCoverageIgnoreEnd
  }

  /**
   *
   * @param PDO $connection
   * @param string $tableName
   * @param string $resultType
   * @param string $condition
   * @param bool $expand
   * @param array $params
   * @throws DatabaseException
   * @return object|NULL
   */
  public function findOne(PDO $connection, string $tableName, string $resultType, bool $expand = true, string $condition = "", array &$params) :?object {
    $query = 'SELECT * FROM `'.$tableName.'`';
    if (isset($condition) && strlen($condition) > 0) $query.=" WHERE ".$condition;
    $query.=" LIMIT 0,1";

    $stmt = $connection->prepare($query);
    if (!$stmt) throw new DatabaseException("Failed to parse query: ".$query);

    foreach ($params as $key => &$value) {
      $stmt->bindParam($key, $value);
    }

    try {
      if (!$stmt->execute()) {
        // @codeCoverageIgnoreStart
        Utils::logError("Error in query",$query,$params,$stmt->errorInfo());
        return null;
        // @codeCoverageIgnoreEnd
      }
      $results = $stmt->fetchAll(PDO::FETCH_CLASS, $resultType);
      foreach ($results as $result) {
        $result->reform($expand);
        return $result;
      }
      // @codeCoverageIgnoreStart
    } catch (Exception $e) {
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $stmt->errorInfo());
      // @codeCoverageIgnoreEnd
    }
    return null;
  }

  /**
   *
   * @return PDO
   */
  public function beginTransaction() :PDO {
    $connection = new PDO(ConfigService::get("acoby_db_dsn"), ConfigService::get("acoby_db_username"), ConfigService::get("acoby_db_password"));
    if (!$connection) {
      // @codeCoverageIgnoreStart
      $error = createError(500, "could not open database.");
      Utils::logError(print_r($error,true));

      header("Content-Type: application/json");
      echo json_encode($error);
      exit;
      // @codeCoverageIgnoreEnd
    }
    $connection->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
    $connection->beginTransaction();
    return $connection;
  }

  /**
   *
   * @param PDO $connection
   * @return bool
   */
  public function commit(PDO $connection) :bool {
    return $connection->commit();
  }

  /**
   *
   * @codeCoverageIgnore
   * @param PDO $connection
   * @return bool
   */
  public function rollback(PDO $connection) :bool {
    return $connection->rollBack();
  }
}