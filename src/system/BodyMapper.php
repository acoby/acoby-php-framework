<?php
declare(strict_types=1);

namespace acoby\system;

use acoby\exceptions\IllegalArgumentException;
use JsonMapper;

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
    } catch (\Exception $error) {
      throw new IllegalArgumentException($error->getMessage());
      // @codeCoverageIgnoreStart
    } catch (\Error $error) {
      throw new IllegalArgumentException($error->getMessage());
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
      return $this->mapObject($mixed, $object);
    } catch (\Exception $error) {
      throw new IllegalArgumentException($error->getMessage());
      // @codeCoverageIgnoreStart
    } catch (\Error $error) {
      throw new IllegalArgumentException($error->getMessage(),null,$error);
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
    } catch (\Exception $error) {
      throw new IllegalArgumentException($error->getMessage());
    } catch (\Error $error) {
      throw new IllegalArgumentException($error->getMessage());
      // @codeCoverageIgnoreEnd
    }
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
    } catch (\Exception $error) {
      Utils::logException("Cannot map JSON String to Object", $error);
      throw new IllegalArgumentException($error->getMessage());
    } catch (\Error $error) {
      Utils::logException("Cannot map JSON String to Object", $error);
      throw new IllegalArgumentException($error->getMessage());
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
}