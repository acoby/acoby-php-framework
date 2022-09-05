<?php
declare(strict_types=1);

namespace acoby\system;

use Throwable;
use DateTime;

class Utils {
  /**
   * Sucht am Beginn eines String nach einem Teilstring
   */
  public static function startsWith(string $haystack, string $needle) :bool {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
  }

  /**
   * Sucht am Ende eines Strings nach einem Teilstring
   */
  public static function endsWith(string $haystack, string $needle) :bool {
    $length = strlen($needle);
    if ($length == 0) {
      return true;
    }

    return (substr($haystack, -$length) === $needle);
  }

  public static function isEmpty(string $value = null) :bool {
    return $value === null || strlen(trim($value)) === 0;
  }

  /**
   * Generate a random string, using a cryptographically secure
   * pseudorandom number generator (random_int)
   *
   * For PHP 7, random_int is a PHP core function
   * For PHP 5.x, depends on https://github.com/paragonie/random_compat
   *
   * @param int $length      How many characters do we want?
   * @param string $keyspace A string of all possible characters
   *                         to select from
   * @return string
   */
  public static function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    if ($max < 1) {
      // @codeCoverageIgnoreStart
      throw new \Exception('$keyspace must be at least two characters long');
      // @codeCoverageIgnoreEnd
    }
    for ($i = 0; $i < $length; ++$i) {
      $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
  }

  /**
   *
   * @param string $value
   * @return bool
   */
  public static function isEnabled(string $value) :bool {
    return (strtolower($value) === "true" || strtolower($value) === "on" || strtolower($value) === "yes" || $value === "1");
  }

  /**
   * @param bool $value
   * @return string
   */
  public static function bool2str(bool $value) :string {
    if ($value === TRUE) return "true"; else return "false";
  }

  /**
   *
   * @param \stdClass $source
   * @param object $destination
   * @return object
   */
  public static function cast(\stdClass $source, $destination) {
    $sourceReflection = new \ReflectionObject($source);
    $sourceProperties = $sourceReflection->getProperties();
    foreach ($sourceProperties as $sourceProperty) {
      $name = $sourceProperty->getName();
      $destination->{$name} = $source->$name;
    }
    return $destination;
  }

  /**
   * @codeCoverageIgnore
   * @param string $message
   * @param string $query
   * @param array $params
   * @param array $errorInfo
   */
  public static function logError(string $message, string $query = null, array $params = null, array $errorInfo = null) {
    $message = "[ERROR] ".$message;
    if ($query !== null) $message .= " in query ".$query;
    if ($params !== null) $message .= " with params ".print_r($params,true);
    if ($errorInfo !== null) $message .= " with error info ".print_r($errorInfo,true);

    error_log($message);
  }


  /**
   * @codeCoverageIgnore
   * @param string $message
   * @param \Throwable $throwable
   */
  public static function logException(string $comment, Throwable $throwable = null) :void {
    $message = "[ERROR] ".$comment;
    if ($throwable !== null) {
      if ($comment !== $throwable->getMessage()) $message.= " ".$throwable->getMessage();
      $message.= " in ".$throwable->getFile().":".$throwable->getLine();
    }
    error_log($message);
    if ($throwable !== null) {
      error_log("[TRACE] ".$throwable->getTraceAsString());
    }
  }

  /**
   * @codeCoverageIgnore
   * @param string $message
   */
  public static function logDebug(string $message) {
    global $ACOBY_CONFIG;
    if ($ACOBY_CONFIG["acoby_environment"] !== "test") {
      error_log("[DEBUG] ".$message);
    }
  }
  
  
  /**
   *
   * @param string $string
   * @return string
   */
  public static function asString(string $string = null, string $defaultString = "") :string {
    if ($string === null) return $defaultString;
    return $string;
  }
  
  /**
   *
   * @param string $string
   * @param int $maxLength
   * @return string
   */
  public static function shorten(string $string, int $maxLength = 31000, string $skipText = "\n...skipped...\n") :string {
    $strlen = strlen($string);
    if ($strlen > $maxLength) {
      $part = ($maxLength / 2)-15;
      return substr($string,0,$part-3).$skipText.substr($string,-$part);
    } else {
      return $string;
    }
  }
  
  /**
   *
   * @param string $datetime
   * @param bool $full
   * @return string
   */
  public static function getTimeElapsed(string $datetime, bool $full = false) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
      if ($diff->$k) {
        $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
      } else {
        unset($string[$k]);
      }
    }
    
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
  }
}
