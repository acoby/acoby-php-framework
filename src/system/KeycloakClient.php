<?php
declare(strict_types=1);

namespace acoby\system;

use \acoby\services\ConfigService;
use Fig\Http\Message\StatusCodeInterface;

class KeycloakClient {
  protected $client;
  protected $base_url;
  protected $mapper;
  protected $cache = array();
  
  /** */
  public function __construct(string $base_url) {
    $this->mapper = new BodyMapper();
    $this->base_url = $base_url;
    
    $this->client = new HTTPClient([
        'base_uri' => $this->base_url,
        'http_errors' => false,
        'timeout' => 10
    ]);
  }
  
  /**
   *
   */
  protected function getClientOptions(string $username = null, string $password = null) :array {
    $options = array();
    $options['headers']['User-Agent'] = 'acoby/'.ConfigService::getString("version");
    $options['headers']['Accept'] = 'application/json';
    $options['headers'][HttpHeader::CONTENT_TYPE] = 'application/json';
    return $options;
  }
  
  /**
   * https://sso.acoby.net/auth/realms/acoby-test/protocol/openid-connect/certs
   * 
   * @param string $realm
   * @return string|NULL
   */
  public function getPubkey(string $realm) :?string {
    if (isset($this->cache[$realm])) return $this->cache[$realm];
    
    $path = "/realms/".$realm;
    $response = $this->client->request("GET",$path, $this->getClientOptions());

    if ($response->getStatusCode() === StatusCodeInterface::STATUS_OK) {
      $keys = json_decode($response->getBody()->getContents(),true);
      $publicKey = "-----BEGIN PUBLIC KEY-----\n";
      $publicKey.= $keys["public_key"]."\n";
      $publicKey.= "-----END PUBLIC KEY-----\n";
      $this->cache[$realm] = $publicKey;
      return $publicKey;
    }
    return null;
  }
  
}
