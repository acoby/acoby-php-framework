<?php
declare(strict_types=1);

namespace acoby\middleware;

class OAuthParams {
  /** @var $method string either "basic" or "jwt" */
  public $method;
  /** @var $username string the username*/
  public $username;
  /** @var $password string optional password given by client */
  public $password;
  
  // JWT contents
  /** @var $firstName string a JWT content of firstName */
  public $firstName;
  /** @var $lastName string a JWT content of lastname */
  public $lastName;
  /** @var $email string a JWT content of email */
  public $email;
  /** @var $roles string a JWT content of roles  */
  public $roles;

  /** @var bool $valid */
  public $valid;
}