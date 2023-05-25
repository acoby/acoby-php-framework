<?php
declare(strict_types=1);

namespace acoby\tests;

use PHPUnit\Framework\TestCase;
use acoby\system\BodyMapper;

abstract class BaseTestCase extends TestCase {
  protected $mapper;

  public function setUp() :void {
    $this->mapper = new BodyMapper();
    $this->setUpComponent();
  }
  
  public function setUpComponent() :void {
    // do nothing
  }
  
  public function randomString(int $length=10, string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') :string {
    $charactersLength = strlen($characters);
    $randstring = '';
    for ($i = 0; $i < $length; $i++) $randstring.= $characters[random_int(0, $charactersLength - 1)];
    return $randstring;
  }
  
  public function randomDomain() :string {
    return $this->randomString(10,'abcdefghijklmnopqrstuvwxyz').".".$this->randomString(3,'abcdefghijklmnopqrstuvwxyz');
  }
  
  public function randomEMail() :string {
    return $this->randomString(10,'abcdefghijklmnopqrstuvwxyz')."@".$this->randomDomain();
  }
  
  public function randomNumber(int $min = 1, int $max = 64000) :int {
    return random_int($min, $max);
  }
  
  public function randomIPv4() :string {
    return $this->randomNumber(1,254).".".$this->randomNumber(1,254).".".$this->randomNumber(1,254).".".$this->randomNumber(1,254);
  }
  
  public function randomIPv6() :string {
    $pattern = '0123456789abcdef';
    return $this->randomString(4,$pattern).":".$this->randomString(4,$pattern).":".$this->randomString(4,$pattern).":".$this->randomString(4,$pattern)."::1";
  }
  
  public function randomCountry() :string {
    return $this->randomString(2,'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
  }
}
