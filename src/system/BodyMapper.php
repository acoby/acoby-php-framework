<?php
declare(strict_types=1);

namespace acoby\system;

use acoby\exceptions\IllegalArgumentException;
use JsonMapper;
use Throwable;

class BodyMapper {
  private $mapper;
  
  public function __construct() {
    $this->mapper = new JsonMapper();
  }
  
  /**
   * Converts JSON string into array
   *
   * @param string $body
   * @throws IllegalArgumentException
   * @return array
   */
  public static function decode(?string $body) :array {
    if ($body === NULL || $body === "") return array();
    try {
      $mixed = json_decode($body,true,1000,JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_BIGINT_AS_STRING);
      if ($mixed !== NULL && is_string($mixed)) {
        // @codeCoverageIgnoreStart
        $mixed = json_decode($mixed, true);
        // @codeCoverageIgnoreEnd
      }
      if ($mixed === NULL) {
        // @codeCoverageIgnoreStart
        throw new IllegalArgumentException(json_last_error_msg());
        // @codeCoverageIgnoreEnd
      }
      return $mixed;
      // @codeCoverageIgnoreStart
    } catch (Throwable $throwable) {
      throw new IllegalArgumentException($throwable->getMessage(),$throwable->getCode(),$throwable);
    }
    // @codeCoverageIgnoreEnd
    return array();
  }
  
  /**
   * Mappen eines String-JSON auf eine Klasse. Gibt es beim Parsen oder Objekt-machen einen Fehler, wird eine Exception geworfen
   *
   * @param string $body
   * @param object $object
   * @throws IllegalArgumentException::
   * @return object
   */
  public function map(string $body, object $object) :object {
    try {
      $mixed = json_decode($body,null,1000,JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_BIGINT_AS_STRING);
      if ($mixed !== NULL && is_string($mixed)) {
        $mixed = json_decode($mixed);
      }
      if ($mixed === NULL) {
        throw new IllegalArgumentException(json_last_error_msg());
      }
      return Utils::cast($mixed, $object);
      // @codeCoverageIgnoreStart
    } catch (Throwable $throwable) {
      throw new IllegalArgumentException($throwable->getMessage(),$throwable->getCode(),$throwable);
    }
    // @codeCoverageIgnoreEnd
  }
  
  /**
   * Mappen eines Mixed Object auf eine Klasse. Gibt es beim Parsen oder Objekt-machen einen Fehler, wird eine Exception geworfen
   *
   * @param mixed $body
   * @param object $object
   * @throws IllegalArgumentException
   * @return object
   */
  public function mapObject($body, object $object) :object {
    try {
      return $this->mapper->map($body, $object);
      // @codeCoverageIgnoreStart
    } catch (Throwable $throwable) {
      throw new IllegalArgumentException($throwable->getMessage(),$throwable->getCode(),$throwable);
      // @codeCoverageIgnoreEnd
    }
  }
  
  /**
   *
   * @param string $body
   * @param string $class
   * @return array
   */
  public function mapList(string $body, string $class) :array {
    $response = array();
    $items = json_decode($body,true);
    foreach ($items as $item) {
      $body = json_encode($item);
      $response[] = $this->map($body, new $class);
    }
    return $response;
  }
  
  /**
   * Mappen eines Array auf eine Klasse. Gibt es beim Parsen oder Objekt-machen einen Fehler, wird eine Exception geworfen
   *
   * @param array $body
   * @param object $object
   * @throws IllegalArgumentException
   * @return object
   */
  public function mapArray(array $body, object $object) :object {
    try {
      $mixed = json_decode(json_encode($body),false,100,JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_BIGINT_AS_STRING);
      return $this->mapper->map($mixed, $object);
      // @codeCoverageIgnoreStart
    } catch (Throwable $throwable) {
      Utils::logException("Cannot map JSON String to Object", $throwable);
      throw new IllegalArgumentException($throwable->getMessage(),$throwable->getCode(),$throwable);
    }
    // @codeCoverageIgnoreEnd
  }
  
  /**
   * Return a JSON string from an array
   *
   * @param array $data
   * @return string
   */
  public function mapJSON(array $data) :string {
    return json_encode($data);
  }
  
  /**
   *
   * @param object $object
   * @return string
   */
  public function toString(object $object) :string {
    return json_encode($object);
  }
}