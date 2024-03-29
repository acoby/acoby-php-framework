<?php
declare(strict_types=1);

namespace acoby\system;

use ReflectionClass;
use ReflectionException;

abstract class BaseEnum {
  private static $constCacheArray = NULL;

  private function __construct(){}

  /**
   * @throws ReflectionException
   */
  private static function getConstants() :array {
    // @codeCoverageIgnoreStart
    if (self::$constCacheArray == NULL) {
      self::$constCacheArray = [];
    }
    $calledClass = get_called_class();
    if (!array_key_exists($calledClass, self::$constCacheArray)) {
      $reflect = new ReflectionClass($calledClass);
      self::$constCacheArray[$calledClass] = $reflect->getConstants();
    }
    return self::$constCacheArray[$calledClass];
    // @codeCoverageIgnoreEnd
  }

  /**
   * @throws ReflectionException
   */
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

  /**
   * @throws ReflectionException
   */
  public static function isValidValue(int $value, bool $strict = true) :bool {
    // @codeCoverageIgnoreStart
    $values = array_values(self::getConstants());
    return in_array($value, $values, $strict);
    // @codeCoverageIgnoreEnd
  }

  /**
   * @throws ReflectionException
   */
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