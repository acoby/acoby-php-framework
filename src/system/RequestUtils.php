<?php
declare(strict_types=1);

namespace acoby\system;

use Psr\Http\Message\ServerRequestInterface;
use acoby\models\RESTStatus;
use acoby\services\ConfigService;
use Exception;
use Throwable;
use acoby\models\RESTError;
use acoby\models\RESTResult;

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
   * @param string|null $defaultValue
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

  /**
   *
   * @param array $args
   * @param string $name
   * @param string|null $defaultValue
   * @return string|NULL
   */
  public static function getStringPathParameter(array $args, string $name, string $defaultValue = null) :?string {
    if (isset($args[$name])) return $args[$name];
    return $defaultValue;
  }
  
  /**
   * Erzeugt ein Standard Error-Array, wie wir es für den JSON Output brauchen.
   *
   * @param int $code
   * @param string $message
   * @return RESTStatus
   */
  public static function createError(int $code, string $message = "") :RESTStatus {
    $error = new RESTError();
    $error->message = $message;
    $status = new RESTStatus();
    $status->code = $code;
    $status->error = $error;
    
    return $status;
  }
  
  /**
   * Erzeugt ein Standard Exception-Array, wie wir es für den JSON Output brauchen.
   *
   * @codeCoverageIgnore
   * @param int $code
   * @param string $message
   * @param Exception $exception
   * @return RESTStatus
   */
  public static function createException(int $code, string $message, Throwable $exception) :RESTStatus {
    error_log("Exception in file ".$exception->getFile().":".$exception->getLine()." with message ".$exception->getMessage()."\n".$exception->getTraceAsString());
    
    $error = new RESTError();
    $error->message = $message;
    
    if (ConfigService::get("acoby_environment") !== "prod") {
      $error->file = $exception->getFile();
      $error->line = $exception->getLine();
      $error->trace =  $exception->getTraceAsString();
      $error->message = $exception->getMessage();
    }
    
    $status = new RESTStatus();
    $status->code = $code;
    $status->error = $error;
    return $status;
  }
  
  /**
   * Erzeugt ein Standard Result-Array, wie wir es für den JSON Output brauchen.
   *
   * @param int $code
   * @param string $message
   * @return RESTStatus
   */
  public static function createResult(int $code, string $message) :RESTStatus {
    $result = new RESTResult();
    $result->message = $message;
    $status = new RESTStatus();
    $status->code = $code;
    $status->result = $result;
    return $status;
  }
}