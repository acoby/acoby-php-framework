<?php
namespace acoby\system\auth;

// https://github.com/daniel-ness/ansible-vault

class AnsibleVault {
  const CIPHER = "aes-256-ctr";

  /**
   * Mit dieser Methode kann man ein Ansible Vault entschlüsseln.
   *
   * @param string $vault
   * @param string $secret
   * @return string
   */
  public static function decrypt(string $vault, string $secret) :string {
    $vault = trim($vault);
    $envelope = explode("\n", $vault);
    $header = "";
    $vault = "";
    foreach ($envelope as $line) {
      if ($header === "") {
        $header = $line;
      } else {
        $vault .= trim($line);
      }
    }
    $vault_parts = explode("\n",hex2bin($vault));
    $salt = hex2bin($vault_parts[0]);
    $hmac = ($vault_parts[1]);
    $ciphertext_raw = hex2bin($vault_parts[2]);

    $key = self::generate_key_initctr(AnsibleVault::CIPHER, $secret, $salt);

    $new_hmac = self::generateHMAC($ciphertext_raw, $key["key2"]);
    $new_hmac = bin2hex($new_hmac);

    if (!hash_equals($new_hmac, $hmac)) {
      throw new \RuntimeException("Invalid HMAC");
    }

    $binaryText = openssl_decrypt($ciphertext_raw, AnsibleVault::CIPHER, $key["key1"], OPENSSL_RAW_DATA, $key["iv"]);

    $padding = ord($binaryText[strlen($binaryText) - 1]);
    $binaryText = substr($binaryText, 0, ($padding * -1));

    // convert from binary data
    $plaintext = '';
    foreach (unpack('c*', $binaryText) as $bin) {
      $char = chr($bin);
      if (ord($char) !== 3) {
        $plaintext .= $char;
      }
    }

    return $plaintext;
  }

  /**
   * Mit dieser Methode kann man ein Ansible Vault erstellen
   *
   * @param string $plaintext
   * @param string $secret
   * @return string
   */
  public static function encrypt(string $plaintext, string $secret, bool $chunk = true) :string {
    $padding = 16 - (strlen($plaintext) % 16);
    $plaintext .= str_repeat(chr($padding), $padding);

    $salt = openssl_random_pseudo_bytes(32);

    $key = self::generate_key_initctr(AnsibleVault::CIPHER, $secret, $salt);

    $ciphertext_raw = openssl_encrypt($plaintext, AnsibleVault::CIPHER, $key["key1"], OPENSSL_RAW_DATA, $key["iv"]);
    $hmac = self::generateHMAC($ciphertext_raw, $key["key2"]);

    $salt = bin2hex($salt);
    $hmac = bin2hex($hmac);
    $crpt = bin2hex($ciphertext_raw);

    $vault = $salt."\n".$hmac."\n".$crpt;
    $ciphertext = bin2hex( $vault );

    return self::vault_format($ciphertext, $chunk);
  }

  /**
   * Generates a Key for a Secret and a Salt
   */
  private static function generate_key_initctr(string $cipher, string $secret, string $salt) :array {
    $key = array();

    $key_length = 32;

    // AES
    $iv_length = openssl_cipher_iv_length($cipher);
    $derivedkey = self::create_key($secret,$salt,$key_length,$iv_length);

    $key["key1"] = substr($derivedkey,0,$key_length);
    $key["key2"] = substr($derivedkey, $key_length,$key_length);
    $key["iv"] = substr($derivedkey,$key_length*2,$iv_length);
    return $key;
  }

  private static function generateHMAC(string $ciphertext, string $key): string {
    return hash_hmac('sha256', $ciphertext, $key, true);
  }

  private static function create_key(string $secret, string $salt, int $keyLength, int $iv_length) :string {
    return hash_pbkdf2("sha256", $secret, $salt, 10000, 2* $keyLength + $iv_length, true);
  }

  private static function vault_format(string $ciphertext, bool $chunk = true, string $cipher = "AES256", string $vaultId = "1.1") :string {
    if ($chunk) $ciphertext = chunk_split($ciphertext, 80, "\n");
    return "\$ANSIBLE_VAULT;".$vaultId.";".$cipher."\n".$ciphertext;
  }

}

?>