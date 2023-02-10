<?php
declare(strict_types=1);

namespace acoby\system;

use acoby\BaseTestCase;

class DatabaseMapperTest extends BaseTestCase {
  public function testAddSelectString() {
    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = "value";
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key);
    $this->assertNotEquals($query, $newQuery);
  }

  public function testAddSelectInteger() {
    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = 1;
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key, "integer");
    $this->assertNotEquals($query, $newQuery);

    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = ">=1";
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key, "integer");
    $this->assertNotEquals($query, $newQuery);

    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = ">1";
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key, "integer");
    $this->assertNotEquals($query, $newQuery);

    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = "<1";
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key, "integer");
    $this->assertNotEquals($query, $newQuery);

    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = "<=1";
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key, "integer");
    $this->assertNotEquals($query, $newQuery);

    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = 1;
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key, "integer", true);
    $this->assertNotEquals($query, $newQuery);
  }

  public function testAddSelectDatetime() {
    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = "2021-06-30 00:00:00";
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key, "datetime");
    $this->assertNotEquals($query, $newQuery);

    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = ">2021-06-30 00:00:00";
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key, "datetime");
    $this->assertNotEquals($query, $newQuery);

    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = ">=2021-06-30 00:00:00";
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key, "datetime");
    $this->assertNotEquals($query, $newQuery);

    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = "<2021-06-30 00:00:00";
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key, "datetime");
    $this->assertNotEquals($query, $newQuery);

    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = "<=2021-06-30 00:00:00";
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key, "datetime");
    $this->assertNotEquals($query, $newQuery);

    $query = "WHERE 1=1 ";
    $params = array();
    $key = "key";
    $var = "2021-06-30 00:00:00";
    $newQuery = DatabaseMapper::getInstance()->addSelectParam($query, $params, $var, $key, "datetime", true);
    $this->assertNotEquals($query, $newQuery);
  }
}