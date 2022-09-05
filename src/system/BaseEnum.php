<?php
namespace acoby\system;

abstract class BaseEnum {
  private static $constCacheArray = NULL;

  private function __construct(){}

  private static function getConstants() :array {
    // @codeCoverageIgnoreStart
    if (self::$constCacheArray == NULL) {
      self::$constCacheArray = [];
    }
    $calledClass = get_called_class();
    if (!array_key_exists($calledClass, self::$constCacheArray)) {
      $reflect = new \ReflectionClass($calledClass);
      self::$constCacheArray[$calledClass] = $reflect->getConstants();
    }
    return self::$constCacheArray[$calledClass];
    // @codeCoverageIgnoreEnd
  }

  public static function isValidName(string $name, bool $strict = false) :bool {
    // @codeCoverageIgnoreStart
    $constants = self::getConstants();

    if ($strict) {
      return array_key_exists($name, $constants);
    }

    $keys = array_map('strtolower', array_keys($constants));
    return in_array(strtolower($name), $keys);
    // @codeCoverageIgnoreEnd
  }

  public static function isValidValue(int $value, bool $strict = true) :bool {
    // @codeCoverageIgnoreStart
    $values = array_values(self::getConstants());
    return in_array($value, $values, $strict);
    // @codeCoverageIgnoreEnd
  }

  public static function getItems() :array {
    $items = array();
    $constants = self::getConstants();
    foreach ($constants as $key => $value) {
      $item = array();
      $item["value"] = $key;
      $item["title"] = $value;
      $items[] = $item;
    }
    return $items;
  }
}