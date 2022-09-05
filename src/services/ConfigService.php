<?php
declare(strict_types=1);

namespace acoby\services;

use acoby\system\Utils;

class ConfigService {
  public static function set(string $key, string $value) :void {
    global $ACOBY_CONFIG;
    if ($ACOBY_CONFIG === null) $ACOBY_CONFIG = array();
    $ACOBY_CONFIG[$key] = $value;
  }

  public static function unset(string $key) :void {
    global $ACOBY_CONFIG;
    unset($ACOBY_CONFIG[$key]);
  }

  public static function get(string $key, string $defaultValue = null) :?string {
    global $ACOBY_CONFIG;
    if (isset($ACOBY_CONFIG[$key])) return $ACOBY_CONFIG[$key];
    return $defaultValue;
  }

  public static function setArray(string $key, array $value) :void {
    global $ACOBY_CONFIG;
    if ($ACOBY_CONFIG === null) $ACOBY_CONFIG = array();
    $ACOBY_CONFIG[$key] = $value;
  }

  public static function getArray(string $key, array $defaultValue = null) :?array {
    global $ACOBY_CONFIG;
    if (isset($ACOBY_CONFIG[$key])) return $ACOBY_CONFIG[$key];
    return $defaultValue;
  }

  public static function getString(string $key, string $defaultValue = null) :?string {
    return ConfigService::get($key,$defaultValue);
  }

  public static function getInt(string $key, int $defaultValue = 0) :int {
    return intval(ConfigService::get($key,strval($defaultValue)));
  }

  public static function getBool(string $key, bool $defaultValue = false) :bool {
    return Utils::isEnabled(ConfigService::get($key,Utils::bool2str($defaultValue)));
  }

  public static function contains(string $key) :bool {
    global $ACOBY_CONFIG;
    return isset($ACOBY_CONFIG[$key]);
  }
}