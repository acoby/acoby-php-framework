<?php
declare(strict_types=1);

namespace acoby\system;

use Exception;
use PDO;
use RuntimeException;
use acoby\services\ConfigService;

class VersionManagement {
  private static $instance = null;
  
  public static function getInstance(): VersionManagement {
    if (self::$instance === null) self::$instance = new VersionManagement();
    return self::$instance;
  }
  
  
  /**
   * Sucht in der Datenbank nach einer Tabelle namens version
   * und wenn sie da ist, prüft sie die Versionsnummer und
   * wenn die richtig ist, macht der Script nichts.
   * Ansonsten legt er die Tabelle an und/oder
   * initialisiert die Datenbank-Version passend zu dieser
   * Software-Version
   */
  public function initDatabase() :void {
    $connection = null;
    try {
      $connection = DatabaseMapper::getInstance()->beginTransaction();
      $connection->exec("SET FOREIGN_KEY_CHECKS=0;");
      
      if (!$this->getTable($connection, 'version')) {
        // die Tabelle existiert noch nicht, also anlegen
        
        Utils::logDebug("Initialize database v0");
        $fileName = ConfigService::getString("basedir").'/sql/v0/init.version.sql';
        $this->importSQL($connection, $fileName);
      }
      
      $currentVersion = $this->getVersion($connection);
      $this->_upgrade($connection, $currentVersion);
      
      $connection->exec("SET FOREIGN_KEY_CHECKS=1;");
      
      DatabaseMapper::getInstance()->commit($connection);
      // @codeCoverageIgnoreStart
    } catch (Exception $e) {
      Utils::logError("Exception during database upgrade ".$e->getMessage()." with stack trace ".$e->getTraceAsString(), null, null, $connection->errorInfo());
      
      DatabaseMapper::getInstance()->rollback($connection);
      if (ConfigService::getString("acoby_environment") !== "prod") {
        echo "Exception during database upgrade ".$e->getMessage()." with stack trace ".$e->getTraceAsString();
      } else {
        exit();
      }
      // @codeCoverageIgnoreEnd
    }
  }
  
  /**
   *
   */
  public function clearDatabase() :void {
    $connection = null;
    try {
      $connection = DatabaseMapper::getInstance()->beginTransaction();
      
      Utils::logDebug("Removing tables");
      
      $query = $connection->query('SHOW TABLES');
      $result = $query->fetchAll(PDO::FETCH_COLUMN);
      $connection->exec("SET FOREIGN_KEY_CHECKS=0");
      foreach ($result as $table) {
        $connection->exec("DROP TABLE `".$table."`");
      }
      
      $connection->exec("SET FOREIGN_KEY_CHECKS=1");
      $connection->commit();
      // @codeCoverageIgnoreStart
    } catch (Exception $e) {
      Utils::logError("Exception during database removal ".$e->getMessage()." with stack trace ".$e->getTraceAsString(), null, null, $connection->errorInfo());
      
      DatabaseMapper::getInstance()->rollback($connection);
      if (ConfigService::getString("acoby_environment") !== "prod") {
        echo "Exception during database removal ".$e->getMessage()." with stack trace ".$e->getTraceAsString();
      } else {
        exit();
      }
      // @codeCoverageIgnoreEnd
    }
  }
  
  /**
   *
   * @codeCoverageIgnore
   */
  public function printTables() :void {
    if (!ConfigService::getBool("acoby_full_state")) return;
    $connection = null;
    try {
      $connection = DatabaseMapper::getInstance()->beginTransaction();
      
      $query = $connection->query('SHOW TABLES');
      $result = $query->fetchAll(PDO::FETCH_COLUMN);
      foreach ($result as $table) {
        error_log("TABLE ".$table);
      }
      $connection->commit();
    } catch (Exception $e) {
      DatabaseMapper::getInstance()->rollback($connection);
    }
  }
  
  /**
   * Sucht nach allen Versionsverzeichnissen. Wenn die Version in der DB
   * anders ist, als die Version im Filesystem, dann werden die Verzeichnisse
   * ab der Version durchgelutscht.
   *
   * @param PDO $conn
   * @param int $currentVersion
   */
  private function _upgrade(PDO $conn, int $currentVersion) {
    $versions = scandir(ConfigService::getString("basedir")."/sql");
    natsort($versions);
    
    foreach ($versions as $dir) {
      if (Utils::startsWith($dir, '.')) continue;
      $version = intval(substr($dir,1));
      
      if ($version > $currentVersion) {
        Utils::logDebug("Upgrading database from v".$currentVersion." to v".$version);
        
        $this->_upgradeVersion($conn,$dir);
        
        // Version hochziehen, wenn erledigt
        $query = 'UPDATE `version` SET `version`='.$version.',`name`="DB v'.$version.'",`created`=now()';
        $conn->exec($query);
        
        $currentVersion = $version;
      }
    }
  }
  
  /**
   * aktualisiert die DB.
   */
  private function _upgradeVersion(PDO $conn, string $version) :void {
    // alle Scripte in sql/v$version ausführen
    $path = ConfigService::getString("basedir").'/sql/'.$version;
    if (!file_exists($path)) {
      // @codeCoverageIgnoreStart
      throw new RuntimeException('Could not upgrade to v'.$version.', upgrade sql files not found');
      // @codeCoverageIgnoreEnd
    }
    $files = scandir($path);
    foreach ($files as $file) {
      if (Utils::startsWith($file, '.')) continue;
      $fileName = $path.'/'.$file;
      $this->importSQL($conn, $fileName);
    }
  }
  
  /**
   * Prüft, ob es die Tabelle gibt
   */
  private function getTable(PDO $conn, string $tableName) :bool {
    $query = $conn->query('SHOW TABLES');
    $result = $query->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($result as $table) {
      if ($table === $tableName) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Liefert die aktuelle Version der DB Tabellen oder 0, wenn keine existiert.
   */
  public function getVersion(PDO $connection = null) :int {
    $response = 0;
    $selfTransaction = false;
    if ($connection === null) {
      $connection = DatabaseMapper::getInstance()->beginTransaction();
      $selfTransaction = true;
    }
    
    $query = $connection->query('SELECT max(`version`) FROM `version`');
    if ($query === FALSE) {
      // @codeCoverageIgnoreStart
      Utils::logError("Could not read current db version");
      // @codeCoverageIgnoreEnd
    } else {
      $result = $query->fetchAll(PDO::FETCH_COLUMN);
      if ($result[0] !== null) $response = $result[0];
    }
    
    if ($selfTransaction) DatabaseMapper::getInstance()->commit($connection);
    return intval($response);
  }
  
  /**
   * Erzeugt die Tabelle auf Basis einer Datei im root-Verzeichnis des Projekts.
   * Siehe /sql/init.*.sql
   */
  private function importSQL(PDO $conn, string $fileName) :void {
    if (!file_exists($fileName)) {
      // @codeCoverageIgnoreStart
      throw new RuntimeException('Could not import file "'.$fileName.'", file not exists');
      // @codeCoverageIgnoreEnd
    }
    $statement = file_get_contents($fileName);
    $ok = $conn->exec($statement);
    if ($ok === FALSE) {
      // @codeCoverageIgnoreStart
      Utils::logError("Could not import SQL file",$fileName,[],$conn->errorInfo());
      // @codeCoverageIgnoreEnd
    }
  }
}