<?php
declare(strict_types=1);

namespace acoby\services;

use acoby\system\Utils;

class ConfigService {
  private static $config = array();
  
  const INTEGER_MAX_VALUE = 2147483648; // we limit to 32bit
  
  public static function isDefined(string $key) :bool {
    return (isset(ConfigService::$config[$key]));
  }
  
  public static function setArray(string $key, array $value) :void {
    ConfigService::$config[$key] = $value;
  }
  public static function set(string $key, string $value) :void {
    ConfigService::$config[$key] = $value;
  }
  public static function setString(string $key, string $value) :void {
    ConfigService::$config[$key] = $value;
  }
  public static function setBool(string $key, bool $value) :void {
    ConfigService::$config[$key] = $value;
  }
  public static function setInt(string $key, int $value) :void {
    ConfigService::$config[$key] = $value;
  }
  
  public static function unset(string $key) :void {
    unset(ConfigService::$config[$key]);
  }
  
  public static function get(string $key, string $defaultValue = null) :?string {
    if (!ConfigService::isDefined($key)) return $defaultValue;
    return ConfigService::$config[$key];
  }
  
  public static function getString(string $key, string $defaultValue = null) :?string {
    return ConfigService::get($key,$defaultValue);
  }
  
  public static function getInt(string $key, int $defaultValue = null) :?int {
    return intval(ConfigService::get($key,strval($defaultValue)));
  }
  
  public static function getBool(string $key, bool $defaultValue = null) :?bool {
    $value = ConfigService::get($key);
    if ($value === null) return $defaultValue;
    return Utils::isEnabled($value);
  }
  
  public static function getArray(string $key, array $defaultValue = null) :?array {
    if (!ConfigService::isDefined($key)) return $defaultValue;
    return ConfigService::$config[$key];
  }
  
  public static function contains(string $key) :bool {
    return ConfigService::isDefined($key);
  }
}