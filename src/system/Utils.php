<?php
declare(strict_types=1);

namespace acoby\system;

use Throwable;
use Exception;
use DateTime;
use acoby\models\RESTStatus;
use acoby\models\RESTResult;
use acoby\models\RESTError;
use acoby\services\ConfigService;

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
  
  /**
   * Verschlüsseln von Daten
   */
  public static function encrypt(string $content, string $key) :?string {
    global $ACOBY_CONFIG;
    if (!in_array($ACOBY_CONFIG["acoby_cipher"], openssl_get_cipher_methods())) {
      // @codeCoverageIgnoreStart
      Utils::logError("could not found cipher ".$ACOBY_CONFIG["acoby_cipher"]);
      return null;
      // @codeCoverageIgnoreEnd
    }
    $ivlen = openssl_cipher_iv_length($ACOBY_CONFIG["acoby_cipher"]);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($content, $ACOBY_CONFIG["acoby_cipher"], $key, OPENSSL_RAW_DATA, $iv);
    
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
    return base64_encode( $iv.$hmac.$ciphertext_raw );
  }
  
  /**
   * Entschlüsseln von Daten
   */
  public static function decrypt(string $ciphertext, string $key) :?string {
    global $ACOBY_CONFIG;
    if (!in_array($ACOBY_CONFIG["acoby_cipher"], openssl_get_cipher_methods())) {
      // @codeCoverageIgnoreStart
      Utils::logError("could not found cipher ".$ACOBY_CONFIG["acoby_cipher"]);
      return null;
      // @codeCoverageIgnoreEnd
    }
    $c = base64_decode($ciphertext);
    $ivlen = openssl_cipher_iv_length($ACOBY_CONFIG["acoby_cipher"]);
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, $sha2len=32);
    $ciphertext_raw = substr($c, $ivlen+$sha2len);
    $content = openssl_decrypt($ciphertext_raw, $ACOBY_CONFIG["acoby_cipher"], $key, OPENSSL_RAW_DATA, $iv);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
    if (hash_equals($hmac, $calcmac)) {
      return $content;
    }
    // @codeCoverageIgnoreStart
    Utils::logError("Could not decrypt ciphertext with given key. Either key is wrong or ciphertext truncated.");
    return null;
    // @codeCoverageIgnoreEnd
  }
  
  /**
   * erzeugt einen Paar aus username und passwort und speichert es ab.
   */
  public static function getCredentials(string $ciphertext, string $key) :array {
    $data = decrypt($ciphertext, $key);
    return explode(" ", $data);
  }
  
  /**
   * erzeugt einen String aus Username + Passwort und speichert es ab.
   */
  public static function setCredentials(string $username, string $password, string $key) :?string {
    return Utils::encrypt($username.' '.$password, $key);
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
  public static function createException(int $code, string $message, Exception $exception) :RESTStatus {
    $error = new RESTError();
    $error->message = $message;
    
    if (ConfigService::get("acoby_environment") !== "prod") {
      $error->file = $exception->getFile();
      $error->line = $exception->getLine();
      $error->trace =  $exception->getTraceAsString();
      $error->message = $exception->getMessage();
    } else {
      // die Exception geben wir im Prod Betrieb nicht raus, aber ins Log
      error_log("Exception in file ".$exception->getFile().":".$exception->getLine()." with message ".$exception->getMessage()."\n".$exception->getTraceAsString());
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
  
  /**
   *
   * @param string $string
   * @return string
   */
  public static function ln(string $string) :string {
    return $string."\n";
  }
}
