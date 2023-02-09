<?php
declare(strict_types=1);

namespace acoby\system;

use acoby\BaseTestCase;
use DateTime;
use DateInterval;

class UtilsTest extends BaseTestCase {
  public function testIsEmpty() {
    $this->assertTrue(Utils::isEmpty(null));
    $this->assertTrue(Utils::isEmpty(""));
    $this->assertFalse(Utils::isEmpty("."));
  }

  public function testGeDomain() {
    $this->assertEquals("example.com", Utils::getDomain("user@example.com"));
    $this->assertNull(Utils::getDomain("user.example.com"));
  }
    
  public function testGetHostname() {
    $this->assertEquals("host", Utils::getHostname("host.domain.tld"));
    $this->assertEquals("host", Utils::getHostname("host"));
  }
  public function testTime() {
    $interval = new DateInterval("P2D");
    $datetime = new DateTime();
    $datetime->sub($interval);
    $this->assertEquals("2 days ago", Utils::getTimeElapsed($datetime->format('c')));
    
    $this->assertEquals("2 days", utils::getTimeDifference($datetime->format('c'), (new DateTime())->format('c')));
  }
  
  public function testCrypt() {
    $content = "Hello World!";
    $key = "MasterKey!01%";
    
    $ciphertext = Utils::encrypt($content, $key);
    $this->assertNotEquals($content, $ciphertext);
    
    $decrypted = Utils::decrypt($ciphertext, $key);
    $this->assertEquals($decrypted, $content);
    
    $username = "username";
    $password = "password";
    $credentials = Utils::setCredentials($username, $password, $key);
    $this->assertNotNull($credentials);
    list($user,$pass) = Utils::getCredentials($credentials, $key);
    
    $this->assertEquals($username, $user);
    $this->assertEquals($password, $pass);
  }
  
  public function testToID() {
    $this->assertEquals("001", Utils::toID(1,3));
  }
}