<?php
declare(strict_types=1);

namespace acoby\system;

use acoby\services\ConfigService;
use acoby\models\AbstractUser;

class SessionManager {
  private static $instance = null;
  protected $mapper;

  const SESSION_KEY_USER = "user";
  const SESSION_KEY_REDIRECT = "redirect";

  /**
   * @codeCoverageIgnore
   */
  protected function __construct() {
    $this->mapper = new BodyMapper();
  }

  public static function getInstance() :SessionManager {
    if (self::$instance === null) {
      // @codeCoverageIgnoreStart
      self::$instance = new SessionManager();
      // @codeCoverageIgnoreEnd
    }
    return self::$instance;
  }

  /**
   *
   * @codeCoverageIgnore
   * @param string $key
   * @return bool
   */
  public function contains(string $key) :bool {
    if (ConfigService::getString("acoby_environment") === "test") {
      ConfigService::contains($key);
    } else {
      return isset($_SESSION[$key]);
    }
  }

  /**
   *
   * @param AbstractUser $user
   */
  public function setUser(AbstractUser $user) :void {
    $this->set(SessionManager::SESSION_KEY_USER,json_encode($user));
  }

  /**
   *
   */
  public function unsetUser() :void {
    $this->unset(SessionManager::SESSION_KEY_USER);
  }
  
  /**
   *
   * @return AbstractUser|NULL
   */
  public function getUser(object $class) :?AbstractUser {
    $userdata = $this->get(SessionManager::SESSION_KEY_USER);
    if ($userdata !== null) {
      return $this->mapper->map($userdata, $class);
    }
    return null;
  }

  /**
   *
   * @param string $key
   * @return string|NULL
   */
  public function get(string $key) :?string {
    if (isset($_SESSION[$key])) {
      // @codeCoverageIgnoreStart
      return $_SESSION[$key];
      // @codeCoverageIgnoreEnd
    }
    return ConfigService::get($key);
  }

  /**
   *
   * @param string $key
   * @param string $value
   */
  public function set(string $key, string $value) :void {
    if (ConfigService::getString("acoby_environment") === "test") {
      ConfigService::set($key, $value);
    } else {
      // @codeCoverageIgnoreStart
      $_SESSION[$key] = $value;
      // @codeCoverageIgnoreEnd
    }
  }

  /**
   * @codeCoverageIgnore
   * @param string $key
   */
  public function unset(string $key) :void {
    ConfigService::unset($key);
    if (isset($_SESSION) && isset($_SESSION[$key])) unset($_SESSION[$key]);
  }
}