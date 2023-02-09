<?php
declare(strict_types=1);

namespace acoby\services;

use acoby\system\Utils;

class ConfigService {
  
  const INTEGER_MAX_VALUE = 2147483648; // we limit to 32bit
  
  public static function isDefined(string $key) :bool {
    global $ACOBY_CONFIG;
    if ($ACOBY_CONFIG === null) $ACOBY_CONFIG = array();
    return (isset($ACOBY_CONFIG[$key]));
  }
  
  public static function get(string $key, string $defaultValue = null) :?string {
    if (!ConfigService::isDefined($key)) return $defaultValue;
    global $ACOBY_CONFIG;
    return $ACOBY_CONFIG[$key];
  }
  
  public static function setArray(string $key, array $value) :void {
    global $ACOBY_CONFIG;
    if ($ACOBY_CONFIG === null) $ACOBY_CONFIG = array();
    $ACOBY_CONFIG[$key] = $value;
  }
  public static function set(string $key, string $value) :void {
    global $ACOBY_CONFIG;
    if ($ACOBY_CONFIG === null) $ACOBY_CONFIG = array();
    $ACOBY_CONFIG[$key] = $value;
  }
  
  public static function unset(string $key) :void {
    global $ACOBY_CONFIG;
    if ($ACOBY_CONFIG === null) $ACOBY_CONFIG = array();
    unset($ACOBY_CONFIG[$key]);
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
    global $ACOBY_CONFIG;
    return $ACOBY_CONFIG[$key];
  }
  
  public static function contains(string $key) :bool {
    return ConfigService::isDefined($key);
  }
}