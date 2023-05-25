<?php
declare(strict_types=1);

namespace acoby;

use PDO;
use PHPUnit\Framework\TestCase;
use acoby\system\BodyMapper;
use acoby\services\ConfigService;
use acoby\system\Utils;

abstract class BaseTestCase extends TestCase {
  protected $mapper;

  public function setUp() :void {
    $this->mapper = new BodyMapper();

    if (ConfigService::contains("acoby_db_dsn") && !Utils::isEmpty(ConfigService::getString("acoby_db_dsn"))) {
      global $pdo;
      $pdo = new PDO(ConfigService::getString("acoby_db_dsn"), ConfigService::getString("acoby_db_username"), ConfigService::getString("acoby_db_password"));
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
  }
  /**
   * Creates a random string
   *
   * @param int $length
   * @param string $characters
   * @return string
   */
  public function randomString(int $length=10, string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') :string {
    $charactersLength = strlen($characters);
    $randstring = '';
    for ($i = 0; $i < $length; $i++) $randstring.= $characters[random_int(0, $charactersLength - 1)];
    return $randstring;
  }
  
  /**
   * Creates a random domain
   *
   * @return string
   */
  public function randomDomain() :string {
    return $this->randomString(10,'abcdefghijklmnopqrstuvwxyz').".".$this->randomString(3,'abcdefghijklmnopqrstuvwxyz');
  }
  
  /**
   * Creates a random email
   *
   * @return string
   */
  public function randomEMail() :string {
    return $this->randomString(10,'abcdefghijklmnopqrstuvwxyz')."@".$this->randomDomain();
  }
  
  /**
   * Creates a random number
   *
   * @param int $min
   * @param int $max
   * @return int
   */
  public function randomNumber(int $min = 1, int $max = 64000) :int {
    return random_int($min, $max);
  }
  
  /**
   * Creates a random IPv4 address
   *
   * @return string
   */
  public function randomIPv4() :string {
    return $this->randomNumber(1,254).".".$this->randomNumber(1,254).".".$this->randomNumber(1,254).".".$this->randomNumber(1,254);
  }
  
  /**
   * Creates a random IPv6 address
   *
   * @return string
   */
  public function randomIPv6() :string {
    $pattern = '0123456789abcdef';
    return $this->randomString(4,$pattern).":".$this->randomString(4,$pattern).":".$this->randomString(4,$pattern).":".$this->randomString(4,$pattern)."::1";
  }
  
  /**
   * Creates a random country
   *
   * @return string
   */
  public function randomCountry() :string {
    return $this->randomString(2,'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
  }
}
