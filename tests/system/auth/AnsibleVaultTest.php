<?php
declare(strict_types=1);

namespace acoby\system\auth;

use acoby\BaseTestCase;
use RuntimeException;

class AnsibleVaultTest extends BaseTestCase {
  public function testVault() {
    $plaintext = "dies ist mein cooler text";
    $secret = "mein passwort";

    $encrypted = AnsibleVault::encrypt($plaintext, $secret, true);
    $this->assertGreaterThan(0, strlen($encrypted));
    $this->assertStringContainsString("ANSIBLE", $encrypted);

    $decrypted = AnsibleVault::decrypt($encrypted, $secret);
    $this->assertEquals($plaintext, $decrypted);

    $encrypted = AnsibleVault::encrypt($plaintext, $secret, false);
    $decrypted = AnsibleVault::decrypt($encrypted, $secret);
    $this->assertEquals($plaintext, $decrypted);
  }

  public function testWrong() {
    $plaintext = "dies ist mein cooler text";
    $secret = "mein passwort";

    $encrypted = AnsibleVault::encrypt($plaintext, $secret, true);

    $this->expectException(RuntimeException::class);
    $wrong_secret = "irgendwas";
    AnsibleVault::decrypt($encrypted, $wrong_secret);
  }
}
