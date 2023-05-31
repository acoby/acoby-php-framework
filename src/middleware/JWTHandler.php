<?php

namespace acoby\middleware;

use acoby\services\UserService;
use acoby\system\Utils;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler {

  /**
   * Validate a OAuth Token
   */
  public static function validateToken(string $token, string $pubkey, string $algorithm = 'RS256') :OAuthParams {
    $params = new OAuthParams();
    try {
      $publicKey = "-----BEGIN PUBLIC KEY-----\n";
      $publicKey.= $pubkey."\n";
      $publicKey.= "-----END PUBLIC KEY-----\n";

      $jwt = JWT::decode($token, new Key($publicKey,$algorithm));

      $params->username = $jwt->preferred_username;
      if (isset($jwt->email)) $params->email = $jwt->email;
      $params->firstName = $jwt->given_name;
      $params->lastName = $jwt->family_name;
      $params->roles = implode(",",JWTHandler::reduceRoles($jwt->realm_access->roles));
      $params->method = "jwt";

      $params->valid = true;
      return $params;

    } catch (Exception $e) {
      Utils::logException("Could not validate token",$e);
    }

    $params->valid = false;
    return $params;
  }

  private static function reduceRoles(array $roles) :array {
    $result = array();

    $isManager = false;
    $isUser = false;

    foreach ($roles as $role) {
      if (UserService::ADMIN === $role) {
        return [$role];
      }
      if (UserService::MANAGER === $role) {
        $result = [$role];
        $isManager = true;
      }
      if (UserService::USER === $role && !$isManager) {
        $result = [$role];
        $isUser = true;
      }
      if (UserService::REPORT === $role && !$isUser && !$isManager) {
        $result = [$role];
      }
    }

    return $result;
  }
}