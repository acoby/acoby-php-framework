<?php
declare(strict_types=1);

namespace acoby\system;

use Throwable;
use Exception;
use DateTime;
use acoby\models\RESTStatus;
use acoby\services\ConfigService;
use Psr\Http\Message\ServerRequestInterface;
use acoby\exceptions\IllegalStateException;

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
    if ($length == 0) return true;
    return (substr($haystack, -$length) === $needle);
  }
  
  /**
   * Check, if var content is empty
   * @param mixed $value
   * @return bool
   */
  public static function isEmpty(string $value = null) :bool {
    return $value === null || strlen(trim($value)) === 0;
  }
  
  /**
   * Returns the value or when the value is null or empty ("") null to reduce 
   * JSON output
   * 
   * @param string $value
   * @return string|NULL
   */
  public static function asNullString(string $value = null) :?string {
    if (Utils::isEmpty($value)) return null;
    return $value;
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
      throw new Exception('$keyspace must be at least two characters long');
      // @codeCoverageIgnoreEnd
    }
    for ($i = 0; $i < $length; ++$i) {
      $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
  }
  
  /**
   * Check, if string is a boolean "true"
   *
   * @param string $value
   * @return bool
   */
  public static function isEnabled($value) :bool {
    if (is_bool($value)) return $value;
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
    if (ConfigService::get("acoby_environment","unknown") !== "test") {
      error_log("[DEBUG] ".$message);
    }
  }
  
  /**
   * @codeCoverageIgnore
   * @param string $message
   */
  public static function logInfo(string $message) :void {
    if (ConfigService::getString("acoby_environment") !== "prod") {
      error_log("[INFO] ".$message);
    }
  }
  
  /**
   *
   * @param string $string
   * @return string
   */
  public static function asString(string $string = null, ?string $defaultString = "") :string {
    if ($string === null) return $defaultString;
    return $string;
  }
  
  /**
   *
   * @param bool $value
   * @param bool $defaultValue
   * @return bool
   */
  public static function asBool($value = null, ?bool $defaultValue = false) :?bool {
    if (!isset($value)) return $defaultValue;
    return Utils::isEnabled($value);
  }
  
  /**
   *
   * @param int $value
   * @param int $defaultValue
   * @return int
   */
  public static function asInt($value = null, ?int $defaultValue = 0) :?int {
    if (!isset($value)) return $defaultValue;
    return $value;
  }
  
  /**
   * Splits a list by comma and returns always a list.
   * 
   * @param string $value
   * @return string[]
   */
  public static function asList(string $value = null) :array {
    if (Utils::isEmpty($value)) return array();
    $values = array();
    foreach (explode(",", $value) as $element) $values[] = trim($element); 
    return $values;
  }
  
  /**
   * Returns a FQDN of name.domain. When name contains not DNS compatible characters, they are encoded.
   * 
   * @param string $name
   * @param string $domain
   * @return string
   */
  public static function asFQDN(string $name, string $domain) :string {
    $name = str_replace(["^","°","`","´"], "", $name); // this value are not matched in preg_replace
    $name = str_replace("--", "-", str_replace(" ", "-", $name));
    return strval(preg_replace('/[^\w^-]+/', '', strtolower($name))).".".$domain;
  }
  
  /**
   *
   * @param int $value
   * @param string $defaultValue
   * @return string|NULL
   */
  public static function asDateTimeStringFromTimestamp($value = null, string $defaultValue = null, string $format = 'c') :?string {
    if (!isset($value)) return $defaultValue;
    if ($value === 0) return $defaultValue;
    return (new DateTime())->setTimestamp((int)$value)->format($format);
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
   * @param string $host
   * @return string
   */
  public static function getHostname(string $host) :string {
    if (strpos($host,'.')>0) {
      return substr($host,0,strpos($host,'.'));
    } else {
      return $host;
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
   * Produces a string that contains the human readable time between two datetime strings.
   *
   * @param string $timeA
   * @param string $timeB
   * @return string
   */
  public static function getTimeDifference(string $timeA, string $timeB) {
    $now = new DateTime($timeB);
    $ago = new DateTime($timeA);
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
    
    return $string ? implode(', ', $string) : '0 seconds';
  }
  
  /**
   * Verschlüsseln von Daten
   */
  public static function encrypt(string $content, string $key) :?string {
    $cipher = ConfigService::get("acoby_cipher","aes-256-cbc");
    try {
      if (!in_array($cipher, openssl_get_cipher_methods())) throw new IllegalStateException("Could not find cipher ".$cipher);
      
      $ivlen = openssl_cipher_iv_length($cipher);
      $iv = openssl_random_pseudo_bytes($ivlen);
      $ciphertext_raw = openssl_encrypt($content, $cipher, $key, OPENSSL_RAW_DATA, $iv);
      
      $hmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
      return base64_encode( $iv.$hmac.$ciphertext_raw );
      // @codeCoverageIgnoreStart
    } catch (Throwable $exception) {
      Utils::logException("Could not encrypt ciphertext",$exception);
    }
    return null;
    // @codeCoverageIgnoreEnd
  }
  
  /**
   * Entschlüsseln von Daten
   */
  public static function decrypt(string $ciphertext, string $key) :?string {
    $cipher = ConfigService::get("acoby_cipher","aes-256-cbc");
    try {
      if (!in_array($cipher, openssl_get_cipher_methods())) throw new IllegalStateException("Could not find cipher ".$cipher);
      
      $c = base64_decode($ciphertext);
      $ivlen = openssl_cipher_iv_length($cipher);
      $iv = substr($c, 0, $ivlen);
      $hmac = substr($c, $ivlen, $sha2len=32);
      $ciphertext_raw = substr($c, $ivlen+$sha2len);
      $content = openssl_decrypt($ciphertext_raw, $cipher, $key, OPENSSL_RAW_DATA, $iv);
      $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
      if (hash_equals($hmac, $calcmac)) {
        return $content;
      }
      // @codeCoverageIgnoreStart
    } catch (Throwable $exception) {
      Utils::logException("Could not decrypt ciphertext",$exception);
    }
    return null;
    // @codeCoverageIgnoreEnd
  }
  
  /**
   * erzeugt einen Paar aus username und passwort und speichert es ab.
   */
  public static function getCredentials(string $ciphertext, string $key) :array {
    $data = Utils::decrypt($ciphertext, $key);
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
   * @codeCoverageIgnore
   * @param int $code
   * @param string $message
   * @return RESTStatus
   * @deprecated please use RequestUtils::createError
   */
  public static function createError(int $code, string $message = "") :RESTStatus {
    return RequestUtils::createError($code, $message);
  }
  
  /**
   * Erzeugt ein Standard Exception-Array, wie wir es für den JSON Output brauchen.
   *
   * @codeCoverageIgnore
   * @param int $code
   * @param string $message
   * @param Exception $exception
   * @return RESTStatus
   * @deprecated please use RequestUtils::createException
   */
  public static function createException(int $code, string $message, Throwable $exception) :RESTStatus {
    return RequestUtils::createException($code, $message, $exception);
  }
  
  /**
   * Erzeugt ein Standard Result-Array, wie wir es für den JSON Output brauchen.
   *
   * @codeCoverageIgnore
   * @param int $code
   * @param string $message
   * @return RESTStatus
   * @deprecated please use RequestUtils::createResult
   */
  public static function createResult(int $code, string $message) :RESTStatus {
    return RequestUtils::createResult($code, $message);
  }
  
  
  /**
   * @codeCoverageIgnore
   * @param ServerRequestInterface $request
   * @param string $name
   * @param bool $defaultValue
   * @return bool
   * @deprecated please use RequestUtils::getBooleanQueryParameter
   */
  public static function getBooleanQueryParameter(ServerRequestInterface $request, string $name, bool $defaultValue) :bool {
    return RequestUtils::getBooleanQueryParameter($request, $name, $defaultValue);
  }
  
  /**
   * @codeCoverageIgnore
   * @param ServerRequestInterface $request
   * @param string $name
   * @param int $defaultValue
   * @return int
   * @deprecated please use RequestUtils::getIntegerQueryParameter
   */
  public static function getIntegerQueryParameter(ServerRequestInterface $request, string $name, int $defaultValue) :int {
    return RequestUtils::getIntegerQueryParameter($request, $name, $defaultValue);
  }
  
  /**
   * @codeCoverageIgnore
   * @param ServerRequestInterface $request
   * @param string $name
   * @param string $defaultValue
   * @return string|NULL
   * @deprecated please use RequestUtils::getStringQueryParameter
   */
  public static function getStringQueryParameter(ServerRequestInterface $request, string $name, string $defaultValue = null) :?string {
    return RequestUtils::getStringQueryParameter($request, $name, $defaultValue);
  }
  
  /**
   * @codeCoverageIgnore
   * @param array $args
   * @param string $name
   * @param string $defaultValue
   * @return string|NULL
   * @deprecated please use RequestUtils::getStringPathParameter
   */
  public static function getStringPathParameter(array $args, string $name, string $defaultValue = null) :?string {
    return RequestUtils::getStringPathParameter($args, $name, $defaultValue);
  }
  
  /**
   * @codeCoverageIgnore
   * @param string $string
   * @return string
   * @deprecated please do not use the method anymore
   */
  public static function ln(string $string) :string {
    return $string."\n";
  }
  
  /**
   * Converts a number into a string with zero-prefix
   *
   * @param int $value
   * @return string
   */
  public static function toID(int $value, int $length = 2) :string {
    return "".str_pad("".$value, $length, "0", STR_PAD_LEFT);
  }
  
  /**
   * returns the domain part of an email address
   *
   * @param string $email
   * @return string|NULL
   */
  public static function getDomain(string $email) :?string {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $split = explode('@', $email);
      return array_pop($split);
    }
    return null;
  }
  
  /**
   * Returns a null safe JSON valid date time
   * 
   * @param string $dateTime
   * @param string $format
   * @return string|NULL
   */
  public static function getJSONDateTime(?string $dateTime, string $format = 'c') :?string {
    if ($dateTime === null) return null;
    return (new Datetime($dateTime))->format($format);
  }
  
  /**
   * Verifies if the given DateTime is really in the given format.
   * 
   * @param string $dateTime
   * @param string $format
   * @return bool
   */
  public static function isDateTime(?string $dateTime, string $format = 'Y-m-d H:i:s') :bool {
    if ($dateTime === null) return false;
    try {
      $date = new DateTime($dateTime);
      if ($date->format($format) !== $dateTime) return false;
      return true;
    } catch (Exception $e) {
      return false;
    }
  }
}
