<?php
declare(strict_types=1);

namespace acoby\system;

use Fig\Http\Message\StatusCodeInterface;
use PDO;
use PDOStatement;
use RuntimeException;
use acoby\exceptions\DatabaseException;
use Exception;
use acoby\models\AbstractSearch;
use acoby\services\ConfigService;
use Throwable;

class DatabaseMapper {
  private static $instance = null;
  private $columnCache = array();
  
  public static function getInstance() :DatabaseMapper {
    if (self::$instance === null) self::$instance = new DatabaseMapper();
    return self::$instance;
  }

  /**
   * Returns an array with all columns of the given table
   *
   * @param PDO $connection
   * @param string $tableName
   * @return string[]
   *@throws DatabaseException|Exception
   */
  private function getColumns(PDO $connection, string $tableName) :array {
    if (isset($this->columnCache[$tableName])) return $this->columnCache[$tableName];

    $query = "SELECT * FROM `".$tableName."` LIMIT 0";
    $result = $connection->query($query);
    if ($result === false) throw new DatabaseException("Table ".$tableName." is not available");
    $columns = array();
    for ($i = 0; $i < $result->columnCount(); $i++) {
      $col = $result->getColumnMeta($i);
      $columns[] = $col['name'];
    }
    
    $this->columnCache[$tableName] = $columns;
    return $columns;
  }

  /**
   * Fügt an den Insert String $query für den Schlüssel in $key den Wert in $var an.
   *
   * @param array $query
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
   * @param string $tableName
   * @param object $object
   * @return PDOStatement|NULL
   * @throws Exception
   */
  public function insert(PDO $connection, string $tableName, object $object) :?PDOStatement {
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
          if (ConfigService::getString("") === "dummy") {
            Utils::logInfo("WARN: the class field: ".get_class($object)."->".$field." (".gettype($object->$field).") has an value, but it has no pendant in table ".$tableName);
          }
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
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $q, $params, $stmt->errorInfo());
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
   * @param string|null $tableName
   */
  public function addUpdateParam(string &$query, array &$params, &$var, string $key, string $tableName = null) :void {
    if (!isset($var)) return;
    if (!Utils::endsWith($query, ' SET ')) $query .=" ,";
    if ($tableName === null) $table = ""; else $table = "`".$tableName."`.";
    $query .= $table."`".$key."` = :".$key." ";
    $params[":".$key] = $var;
  }

  /**
   * Fügt an den String $query für den Schlüssel in $key den Wert in $var an.
   *
   * @param string $key
   * @param string $operator
   * @param bool $orNull
   * @param string|null $tableName
   * @return string
   */
  private function addToQuery(string $key, string $operator, bool $orNull, string $tableName = null) :string {
    if ($tableName === null) $table = ""; else $table = "`".$tableName."`.";
    if ($orNull) {
      return " (".$table."`".$key."` ".$operator." :".$key." OR ".$table."`".$key."` IS NULL) ";
    } else {
      return " ".$table."`".$key."` ".$operator." :".$key." ";
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
   * @param bool $orNull
   * @param string|null $tableName
   */
  public function addSelectParam(string &$query, array &$params, &$var, string $key, string $type="string", bool $orNull = false, string $tableName = null) :void {
    if (!isset($var)) return;
    if ($tableName === null) $table = ""; else $table = "`".$tableName."`.";
    if ($type === "string") {
      if ($var === "null") {
        $query .= " AND ".$table."`".$key." IS NULL `";
      } else if (strpos($var,"*")===false) {
        $query .= " AND ".$this->addToQuery($key,"=",$orNull, $tableName);
        $params[":".$key] = $var;
      } else {
        $query .= " AND ".$this->addToQuery($key,"LIKE",$orNull, $tableName);
        $params[":".$key] = str_replace("*","%",$var);
      }
    } else  if ($type == "integer") {
      if ($var === "null") {
        $query .= " AND ".$table."`".$key." IS NULL `";
      } else if (is_string($var) && strpos($var,">=")===0) {
        $query .= " AND ".$this->addToQuery($key,">=",$orNull, $tableName);
        $params[":".$key] = str_replace(">=","",$var);
      } else if (is_string($var) && strpos($var,">")===0) {
        $query .= " AND ".$this->addToQuery($key,">",$orNull, $tableName);
        $params[":".$key] = str_replace(">","",$var);
      } else if (is_string($var) && strpos($var,"<=")===0) {
        $query .= " AND ".$this->addToQuery($key,"<=",$orNull, $tableName);
        $params[":".$key] = str_replace("<=","",$var);
      } else if (is_string($var) && strpos($var,"<")===0) {
        $query .= " AND ".$this->addToQuery($key,"<",$orNull, $tableName);
        $params[":".$key] = str_replace("<","",$var);
      } else {
        $query .= " AND ".$this->addToQuery($key,"=",$orNull, $tableName);
        $params[":".$key] = $var;
      }
    } else if ($type == "datetime") {
      if ($var === "null") {
        $query .= " AND ".$table."`".$key." IS NULL `";
      } else if (strpos($var,">=")===0) {
        $query .= " AND ".$this->addToQuery($key,">=",$orNull, $tableName);
        $params[":".$key] = str_replace(">=","",$var);
      } else if (strpos($var,">")===0) {
        $query .= " AND ".$this->addToQuery($key,">",$orNull, $tableName);
        $params[":".$key] = str_replace(">","",$var);
      } else if (strpos($var,"<=")===0) {
        $query .= " AND ".$this->addToQuery($key,"<=",$orNull, $tableName);
        $params[":".$key] = str_replace("<=","",$var);
      } else if (strpos($var,"<")===0) {
        $query .= " AND ".$this->addToQuery($key,"<",$orNull, $tableName);
        $params[":".$key] = str_replace("<","",$var);
      } else {
        $query .= " AND ".$this->addToQuery($key,"=",$orNull, $tableName);
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
   * @return bool
   *@throws DatabaseException|Exception
   */
  public function update(PDO $connection, string $tableName, object $object, string $identifierName, string $identifier) :bool {
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
    if (!$stmt) throw new DatabaseException("Failed to parse query: ".$query);
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
   * @return bool
   */
  public function exec(PDO $connection, string $query, array &$params) :bool {
    try {
      $stmt = $connection->prepare($query);
      if (!$stmt) throw new DatabaseException("Failed to parse query: ".$query." ".print_r($params,true));

      foreach ($params as $key => &$value) $stmt->bindParam($key, $value);

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
   * Physical remove row from database.
   *
   * @param PDO $connection
   * @param string $tableName
   * @param string $identifierName
   * @param string $identifier
   * @throws RuntimeException
   * @return bool
   */
  public function remove(PDO $connection, string $tableName, string $identifierName, string $identifier) :bool {
    $params = array();
    $query = 'DELETE FROM `'.$tableName.'` WHERE 1=1';
    $this->addSelectParam($query, $params, $identifier, $identifierName);
    
    $stmt = $connection->prepare($query);
    if (!$stmt) throw new DatabaseException("Failed to parse query: ".$query);

    foreach ($params as $key => &$value) {
      $stmt->bindParam($key, $value);
    }
    
    // @codeCoverageIgnoreStart
    try {
      if (!$stmt->execute()) {
        Utils::logError("Query failed", $query, $params, $stmt->errorInfo());
        return false;
      }
      return true;
    } catch (Exception $e) {
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $stmt->errorInfo());
    }
    // @codeCoverageIgnoreEnd
    return false;
  }

  /**
   *
   * @param PDO $connection
   * @param string $tableName
   * @param string $identifierName
   * @param string $identifier
   * @param string $condition
   * @return bool
   */
  public function delete(PDO $connection, string $tableName, string $identifierName, string $identifier, string $condition = "`deleted`= now()") :bool {
    $params = array();
    $query = 'UPDATE `'.$tableName.'` SET '.$condition.' WHERE 1=1 ';
    $this->addSelectParam($query, $params, $identifier, $identifierName);
    
    $stmt = $connection->prepare($query);
    if (!$stmt) throw new DatabaseException("Failed to parse query: ".$query);

    foreach ($params as $key => &$value) {
      $stmt->bindParam($key, $value);
    }
    
    // @codeCoverageIgnoreStart
    try {
      if (!$stmt->execute()) {
        Utils::logError("Query failed", $query, $params, $stmt->errorInfo());
        return false;
      }
      return true;
    } catch (Exception $e) {
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $stmt->errorInfo());
    }
    // @codeCoverageIgnoreEnd
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
      return intval($result[0]);
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
   * @param int $fetch_style
   * @param null $fetch_arguments
   * @return array
   */
  public function query(PDO $connection, string $query, array &$params, int $fetch_style = PDO::FETCH_NUM, $fetch_arguments = null) :array {
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
   * @param AbstractSearch $search
   * @param string $resultType
   * @param string|null $orderBy
   * @param string|null $condition
   * @param array|null $conditionalParams
   * @param array|null $joins
   * @return array
   * @throws Exception
   */
  public function search(PDO $connection, string $tableName, AbstractSearch $search, string $resultType, string $orderBy = null, string $condition = null, array $conditionalParams = null, array $joins = null) :array {
    $tableColumns = $this->getColumns($connection, $tableName);
    $classFields = array_keys(get_class_vars(get_class($search)));
    $searchFields = array_keys(get_class_vars(AbstractSearch::class));
    
    if (!isset($search->offset)) $search->offset = 0;
    if (!isset($search->limit)) $search->limit = 10;
    
    $params = array();
    $query = 'SELECT `'.$tableName.'`.* FROM `'.$tableName.'` ';
    if ($joins !== null) {
      foreach ($joins as $joinTable => $joinCondition) {
        $query.= "LEFT JOIN `".$joinTable."` ON ".$joinCondition." ";
      }
    }
    $query.= 'WHERE 1=1 ';
    
    if ($condition !== null && strlen(trim($condition))>0 && $conditionalParams !== null && count($conditionalParams)>0) {
      $query.= ' AND '.$condition." ";
      $params = array_merge($params,$conditionalParams);
    }
    
    foreach ($classFields as $field) {
      if (!in_array($field, $searchFields)) {
        if (in_array($field,$tableColumns)) {
          $this->addSelectParam($query, $params, $search->$field, $field, gettype($search->$field), false, $tableName);
        } else {
          Utils::logInfo("class field: '".get_class($search)."->".$field."' has no pendant in table '".$tableName."' in trace ".(new Exception())->getTraceAsString());
        }
      }
    }
    
    if (in_array("created",$tableColumns)) $this->addSelectParam($query, $params, $search->created, "created", "datetime", false, $tableName);
    if (in_array("changed",$tableColumns)) $this->addSelectParam($query, $params, $search->changed, "changed", "datetime", false, $tableName);
    if (in_array("deleted",$tableColumns)) {
      $this->addSelectParam($query, $params, $search->deleted, "deleted", "datetime", false, $tableName);
      if (!isset($search->deleted)) $query .= " AND `".$tableName."`.`deleted` IS NULL";
    }
    
    if (isset($orderBy)) {
      $query.=" ORDER BY ".$orderBy;
      if (isset($search->reverse) && $search->reverse) $query.=" DESC";
    }
    
    if (isset($search->limit) && is_numeric($search->limit)) {
      $query .= " LIMIT ".intval($search->offset).",".intval($search->limit);
    }
    
    $stmt = $connection->prepare($query);
    if (!$stmt) throw new DatabaseException("Failed to parse query: ".$query);

    foreach ($params as $key => &$value) {
      $stmt->bindParam($key, $value);
    }
    
    try {
      if (!$stmt->execute()) {
        // @codeCoverageIgnoreStart
        Utils::logError("Query failed", $query, $params, $stmt->errorInfo());
        return [];
        // @codeCoverageIgnoreEnd
      }
      $results = $stmt->fetchAll(PDO::FETCH_CLASS, $resultType);
      foreach($results as $result) $result->reform($search->expand);
      
      return $results;
    } catch (Exception $e) {
      // @codeCoverageIgnoreStart
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $stmt->errorInfo());
      // @codeCoverageIgnoreEnd
    }
    return [];
  }


  /**
   *
   * @param PDO $connection
   * @param string $tableName
   * @param string $resultType
   * @param string $condition
   * @param array $params
   * @param bool $expand
   * @param int $limit
   * @param int $offset
   * @param bool|null $hide
   * @param array|null $joins
   * @return array
   */
  public function findAll(PDO $connection, string $tableName, string $resultType, string $condition = "", array $params = [], bool $expand = false, int $limit = 100, int $offset = 0, bool $hide=null, array $joins = null) :array {
    $query = 'SELECT `'.$tableName.'`.* FROM `'.$tableName.'` ';
    if ($joins !== null) {
      foreach ($joins as $joinTable => $joinCondition) {
        $query.= "LEFT JOIN `".$joinTable."` ON ".$joinCondition." ";
      }
    }
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
      Utils::logError("Failed parsing query",$query,$params,$connection->errorInfo());
      throw new DatabaseException("Failed to parse query: ".$query);
    }
    
    foreach ($params as $key => &$value) {
      $stmt->bindParam($key, $value);
    }

    try {
      if (!$stmt->execute()) {
        // @codeCoverageIgnoreStart
        Utils::logError("Error in query",$query,$params,$stmt->errorInfo());
        return [];
        // @codeCoverageIgnoreEnd
      }
      $results = $stmt->fetchAll(PDO::FETCH_CLASS, $resultType);
      foreach ($results as $result) {
        if ($hide !== null) {
          $result->reform($expand,$hide);
        } else {
          $result->reform($expand);
        }
      }
      return $results;
      // @codeCoverageIgnoreStart
    } catch (Exception $e) {
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $stmt->errorInfo());
    }
    return [];
    // @codeCoverageIgnoreEnd
  }

  /**
   * find a Single row, map it to a class on a given condition
   *
   * <pre>
   * $params = array();
   * $params['externalId'] = $instanceId;
   * $condition = "`".ServiceInstance::TABLE_NAME."`.`deleted` IS NULL AND `".ServiceInstance::TABLE_NAME."`.`externalId`=:externalId";
   * if ($customerId !== null) {
   *   $params['customerId'] = $customerId;
   *   $condition.= " AND `".ServiceInstance::TABLE_NAME."`.`customerId`=:customerId";
   * }
   * $join = array();
   * $join[] = [
   *     "table"=>Service::TABLE_NAME,
   *     "condition"=>"LEFT JOIN `".Service::TABLE_NAME."` ON `".ServiceInstance::TABLE_NAME."`.`serviceId` = `".Service::TABLE_NAME."`.`externalId`",
   *     "class"=>Service::class,
   *     "field"=>"service"
   * ];
   * </pre>
   *
   * @param PDO $connection
   * @param string $tableName the table to look for
   * @param string $resultType the result class type
   * @param bool $expand expand the object (uses the internal method to expand the object)
   * @param string $condition the SQL condition to look for, like 'deleted is null and id = :id'
   * @param array $params a key-value map for mapping the prepared statements
   * @param array|null $join with a list of map containing values "table", "class", "field", "condition" for joining a table and mapping it to a class and assign it to a field
   * @param bool|null $hide
   * @return object|NULL
   */
  public function findOne(PDO $connection, string $tableName, string $resultType, bool $expand, string $condition, array &$params, array $join=null, bool $hide=null) :?object {
    $stmt = null;
    try {
      $query = 'SELECT `'.$tableName.'`.*';
      
      if ($join !== null) {
        foreach ($join as $table) {
          $tableColumns = $this->getColumns($connection, $table["table"]);
          foreach ($tableColumns as $column) {
            if ($column === "id") continue;
            $query.= ',`'.$table["table"].'`.`'.$column.'` AS '.$table["table"].'_X_'.$column;
          }
        }
      }
      $query.= ' FROM `'.$tableName.'`';
      if ($join !== null) {
        foreach ($join as $table) {
          $query.= ' '.$table["condition"].' ';
        }
      }
      if (strlen($condition) > 0) $query.=" WHERE ".$condition;
      $query.=" LIMIT 0,1";
      
      $stmt = $connection->prepare($query);
      if (!$stmt) throw new DatabaseException("Failed to parse query: ".$query);

      foreach ($params as $key => &$value) {
        $stmt->bindParam($key, $value);
      }
      
      if (!$stmt->execute()) {
        // @codeCoverageIgnoreStart
        Utils::logError("Query failed", $query, $params, $stmt->errorInfo());
        return null;
        // @codeCoverageIgnoreEnd
      }
      
      $results = $stmt->fetchAll(PDO::FETCH_CLASS, $resultType);
      foreach ($results as $result) {
        if ($join !== null) {
          foreach ($join as $table) {
            $classVars = array_keys(get_class_vars($table["class"]));
            $class = new $table["class"];
            $field = $table["field"];
            foreach ($classVars as $classVar) {
              $rowVar = $table["table"].'_X_'.$classVar;
              if (isset($result->$rowVar)) $class->$classVar = $result->$rowVar;
              unset($result->$rowVar);
            }
            $result->$field = $class;
          }
        }
        if ($hide !== null) {
          $result->reform($expand, $hide);
        } else {
          $result->reform($expand);
        }
        return $result;
      }
    } catch (Exception $e) {
      // @codeCoverageIgnoreStart
      $errorInfo = array();
      if (is_object($stmt)) $errorInfo = $stmt->errorInfo();
      Utils::logError("Exception in query ".$e->getMessage()." trace ".$e->getTraceAsString(), $query, $params, $errorInfo);
      // @codeCoverageIgnoreEnd
    }
    return null;
  }


  /**
   * Returns the number of entries in the given table
   *
   * @param PDO $connection
   * @param string $table the table name to count
   * @param bool $ignoreDeleted true when ignoring deleted fields
   * @return number
   */
  public function getObjectCount(PDO $connection, string $table, bool $ignoreDeleted = true) :int {
    $query = "SELECT count(`id`) FROM `".$table."`";
    if ($ignoreDeleted) $query.= " WHERE `deleted` IS NULL";
    $params = array();
    $counts = $this->query($connection, $query, $params, PDO::FETCH_COLUMN, 0);
    if ($counts === null || count($counts)<1) return 0;
    return intval($counts[0]);
  }

  /**
   * Checks if in the given table a row exists that contains a specific value in a specific column
   *
   * @param PDO $connection
   * @param string $table the table to search for
   * @param string $column the column in the table to search for
   * @param string $value the value in the column in the table to search for
   * @param string|null $externalId ignore rows with that id (to avoiid duplicates)
   * @return bool true, when there is a row with that value in the given column in the given table
   */
  public function existsObject(PDO $connection, string $table, string $column, string $value, string $externalId = null) :bool {
    $params = array();
    $params["value"] = $value;
    $query = "SELECT count(id) FROM `".$table."` WHERE `".$column."` = :value";
    if ($externalId !== null) {
      $query .= " AND externalId <> :externalId";
      $params["externalId"] = $externalId;
    }

    $counts = DatabaseMapper::getInstance()->query($connection, $query, $params, PDO::FETCH_COLUMN, 0);
    if ($counts === null || count($counts)<1) return false;
    return $counts[0] > 0;
  }

  /**
   *
   * @return PDO
   */
  public function beginTransaction() :PDO {
    try {
      $connection = new PDO(ConfigService::get("acoby_db_dsn"), ConfigService::get("acoby_db_username"), ConfigService::get("acoby_db_password"));
      $connection->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
      $connection->beginTransaction();
      return $connection;
    } catch (Throwable $throwable) {
      // @codeCoverageIgnoreStart
      $error = RequestUtils::createError(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, "could not open database.");
      Utils::logError(print_r($error,true));

      header("Content-Type: application/json");
      echo json_encode($error);
      exit;
      // @codeCoverageIgnoreEnd
    }
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