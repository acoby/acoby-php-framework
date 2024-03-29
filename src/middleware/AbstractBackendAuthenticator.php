<?php
declare(strict_types=1);

namespace acoby\middleware;

use acoby\exceptions\IllegalStateException;
use acoby\services\AbstractFactory;
use acoby\models\AbstractUser;

/**
 * This authenticator looks for JWT or HTTP Basic authentication in the request.
 * 
 * @author Thoralf Rickert-Wendt
 */
abstract class AbstractBackendAuthenticator {
  protected $options = [
    "sso_auto_insert" => true,
    "sso_role" => "CUSTOMER"
  ];
  
  /**
   * When there is an SSO login (via JWT) and we don't find a suitable user in the backend,
   * we need to create a new user in the backend. This method is used to use the JWT attributes
   * to create a new user in the backend.
   * 
   * @param OAuthParams $params
   * @param AbstractUser $admin
   * @return AbstractUser|NULL
   */
  public abstract function createSSOUser(OAuthParams $params, AbstractUser $admin) :?AbstractUser;

  /**
   * Will be invoked during authentication.
   *
   * @param OAuthParams $params
   * @return AbstractUser|NULL
   * @throws IllegalStateException
   */
  public function __invoke(OAuthParams $params) :?AbstractUser {
    $username = $params->username;
    if ($username === null) return null;
    
    // prüfen, ob es den User in der internen DB gibt
    $user = AbstractFactory::getUserService()->getUserByName($username,false,false);
    if ($user === null) {
      // wenn es den User nicht gibt, dann kann das am SSO liegen
      if ($this->options["sso_auto_insert"] === true && $params->method == "jwt") {
        // wenn wir SSO verwenden und der User eine bestimmte Rolle darin hat
        // dann legen wir den User intern an.
        if ($params->roles !== null && strpos($params->roles, $this->options["sso_role"]) >= 0) {
          $admin = AbstractFactory::getUserService()->getUserByName("admin");
          // User anlegen
          // TODO Der Nutzer wird bei acoby eingehängt, weil wir keine Idee haben, wer der Nutzer ist.
          $user = $this->createSSOUser($params, $admin);
          if ($user === null) return null;
        } else {
          return null;
        }
      } else {
        return null;
      }
    }
    if ($params->method === "basic") {
      if (password_verify($params->password,$user->password)) {
        return $user;
      }
    } else if ($params->method === "jwt") {
      return $user;
    }

    return null;
  }

}

