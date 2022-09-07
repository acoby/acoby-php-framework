<?php
declare(strict_types=1);

namespace acoby\system;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @codeCoverageIgnore
 * @author thoralf
 */
class RequestUtils {
  private static $instance = null;

  /**
   * @return RequestUtils
   */
  public static function getInstance(): RequestUtils {
    if (self::$instance === null) {
      self::$instance = new RequestUtils();
    }
    return self::$instance;
  }

  /**
   *
   * @param ServerRequestInterface $request
   * @param string $name
   * @param bool $defaultValue
   * @return bool
   */
  public static function getBooleanQueryParameter(ServerRequestInterface $request, string $name, bool $defaultValue) :bool {
    $queries = $request->getQueryParams();
    if (array_key_exists($name, $queries)) {
      $value = urlencode($queries[$name]);
      return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    } else {
      return $defaultValue;
    }
  }

  /**
   *
   * @param ServerRequestInterface $request
   * @param string $name
   * @param int $defaultValue
   * @return int
   */
  public static function getIntegerQueryParameter(ServerRequestInterface $request, string $name, int $defaultValue) :int {
    $queries = $request->getQueryParams();
    if (array_key_exists($name, $queries)) {
      $value = urlencode($queries[$name]);
      return filter_var($value, FILTER_VALIDATE_INT);
    } else {
      return $defaultValue;
    }
  }

  /**
   *
   * @param ServerRequestInterface $request
   * @param string $name
   * @param string $defaultValue
   * @return string
   */
  public static function getStringQueryParameter(ServerRequestInterface $request, string $name, string $defaultValue = null) :?string {
    $queries = $request->getQueryParams();
    if (array_key_exists($name, $queries)) {
      return urlencode($queries[$name]);
    } else {
      return $defaultValue;
    }
  }
}