<?php
declare(strict_types=1);

namespace acoby\middleware;

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tuupola\Http\Factory\ResponseFactory;
use Tuupola\Middleware\DoublePassTrait;
use Tuupola\Middleware\HttpBasicAuthentication\RequestMethodRule;
use Tuupola\Middleware\HttpBasicAuthentication\RequestPathRule;
use Closure;
use Exception;
use acoby\system\HttpHeader;
use Fig\Http\Message\StatusCodeInterface;

/**
 *
 * @author Thoralf Rickert-Wendt
 */
class OAuthAuthentication implements MiddlewareInterface {
  use DoublePassTrait;
  
  private $rules;
  private $options = [
      "secure" => true,
      "relaxed" => ["localhost", "127.0.0.1"],
      "path" => null,
      "ignore" => null,
      "realm" => "Protected",
      "authenticator" => null,
      "before" => null,
      "after" => null,
      "error" => null
  ];
  
  /**
   *
   * @param array $options
   */
  public function __construct($options = []) {
    /* Setup stack for rules */
    $this->rules = new \SplStack;
    
    /* Store passed in options overwriting any defaults */
    $this->hydrate($options);
    
    /* If nothing was passed in options add default rules. */
    if (!isset($options["rules"])) {
      $this->rules->push(new RequestMethodRule([
          "ignore" => ["OPTIONS"]
      ]));
    }
    
    /* If path was given in easy mode add rule for it. */
    if (null !== $this->options["path"]) {
      $this->rules->push(new RequestPathRule([
          "path" => $this->options["path"],
          "ignore" => $this->options["ignore"]
      ]));
    }
    
  }
  
  /**
   * Process a request in PSR-15 style and return a response.
   */
  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    $host = $request->getUri()->getHost();
    $scheme = $request->getUri()->getScheme();
    
    /* If rules say we should not authenticate call next and return. */
    if (false === $this->shouldAuthenticate($request)) {
      return $handler->handle($request);
    }
    
    /* HTTP allowed only if secure is false or server is in relaxed array. */
    if ("https" !== $scheme && true === $this->options["secure"]) {
      $allowedHost = in_array($host, $this->options["relaxed"]);
      
      /* if 'headers' is in the 'relaxed' key, then we check for forwarding */
      $allowedForward = false;
      if (in_array("headers", $this->options["relaxed"])) {
        if ($request->getHeaderLine(HttpHeader::X_FORWARDED_PROTO) === "https" && $request->getHeaderLine(HttpHeader::X_FORWARDED_PORT) === "443") {
          $allowedForward = true;
        }
      }
      
      if (!($allowedHost || $allowedForward)) {
        $message = sprintf("Insecure use of middleware over %s denied by configuration.",strtoupper($scheme));
        throw new \RuntimeException($message);
      }
    }
    
    /* Just in case. */
    $params = new OAuthParams();
    
    $matches = array();
    if (preg_match("/Basic\s+(.*)$/i", $request->getHeaderLine(HttpHeader::AUTHORIZATION), $matches)) {
      $explodedCredential = explode(":", base64_decode($matches[1]), 2);
      if (count($explodedCredential) == 2) {
        list($params->username, $params->password) = $explodedCredential;
        $params->method = "basic";
      }
    } else if (preg_match("/Bearer\s+(.*)$/i", $request->getHeaderLine(HttpHeader::AUTHORIZATION), $matches)) {
      $token = $matches[1];
      $params = $this->validateToken($request,$token);
    }
    
    /* Check if user authenticates. */
    $user = $this->options["authenticator"]($params);
    if ($user === null) {
      /* Set response headers before giving it to error callback */
      $arguments = array();
      $arguments["code"] = StatusCodeInterface::STATUS_UNAUTHORIZED;
      $arguments["headers"][] = ["key" => HttpHeader::WWW_AUTHENTICATE, "value" => sprintf('Basic realm="%s"', $this->options["realm"])];
      $arguments["message"] = "Authentication failed";
      $response = (new ResponseFactory)->createResponse($arguments["code"]);
      return $this->processError($response, $arguments);
    } else {
      // verify, that user is not already defined in request (which would be bad)
      if ($request->getAttribute("user") !== null) {
        $arguments = array();
        $arguments["code"] = StatusCodeInterface::STATUS_FORBIDDEN;
        $arguments["message"] = "Overriding 'user' attribute not allowed";
        
        $response = (new ResponseFactory)->createResponse(StatusCodeInterface::STATUS_FORBIDDEN);
        return $this->processError($response, $arguments);
      }
      $request = $request->withAttribute("user", $user);
    }
    
    /* Modify $request before calling next middleware. */
    if (is_callable($this->options["before"])) {
      $response = (new ResponseFactory)->createResponse(StatusCodeInterface::STATUS_OK);
      $before_request = $this->options["before"]($request, $params);
      if ($before_request instanceof ServerRequestInterface) {
        $request = $before_request;
      }
    }
    
    /* Everything ok, call next middleware. */
    $response = $handler->handle($request);
    
    /* Modify $response before returning. */
    if (is_callable($this->options["after"])) {
      $after_response = $this->options["after"]($response, $params);
      if ($after_response instanceof ResponseInterface) {
        return $after_response;
      }
    }
    
    return $response;
  }
  
  /**
   * Validate a OAuth Token
   */
  private function validateToken(ServerRequestInterface $request, string $token) :OAuthParams {
    $params = new OAuthParams();
    try {
      $publicKey = "-----BEGIN PUBLIC KEY-----\n";
      $publicKey.= $this->options["oidc"]["key"]."\n";
      $publicKey.= "-----END PUBLIC KEY-----\n";
      
      $jwt = JWT::decode($token, $publicKey, array('ES256','RS256','RS512'));
      
      $params->username = $jwt->preferred_username;
      if (isset($jwt->email)) $params->email = $jwt->email;
      $params->firstName = $jwt->given_name;
      $params->lastName = $jwt->family_name;
      $params->roles = implode(",",$jwt->realm_access->roles);
      $params->method = "jwt";
      
      return $params;
      
    } catch (Exception $e) {
      error_log($e);
    }
    
    return $params;
  }
  
  /**
   * Hydrate all options from given array.
   */
  private function hydrate(array $data = []): void {
    foreach ($data as $key => $value) {
      /* https://github.com/facebook/hhvm/issues/6368 */
      $key = str_replace(".", " ", $key);
      $method = lcfirst(ucwords($key));
      $method = str_replace(" ", "", $method);
      if (method_exists($this, $method)) {
        /* Try to use setter */
        call_user_func([$this, $method], $value);
      } else {
        /* Or fallback to setting option directly */
        $this->options[$key] = $value;
      }
    }
  }
  
  
  /**
   * Test if current request should be authenticated.
   */
  private function shouldAuthenticate(ServerRequestInterface $request): bool {
    /* If any of the rules in stack return false will not authenticate */
    foreach ($this->rules as $callable) {
      if (false === $callable($request)) {
        return false;
      }
    }
    return true;
  }
  
  
  /**
   * Execute the error handler.
   */
  private function processError(ResponseInterface $response, array $arguments): ResponseInterface {
    if (is_callable($this->options["error"])) {
      $handler_response = $this->options["error"]($response, $arguments);
      if ($handler_response instanceof ResponseInterface) {
        return $handler_response;
      }
    }
    return $response;
  }
  
  /**
   * Set path where middleware should bind to.
   */
  private function path($path): void {
    $this->options["path"] = (array) $path;
  }
  /**
   * Set path which middleware ignores.
   */
  private function ignore($ignore): void {
    $this->options["ignore"] = (array) $ignore;
  }
  
  /**
   * Set the authenticator.
   */
  private function authenticator(callable $authenticator): void {
    $this->options["authenticator"] = $authenticator;
  }
  
  /**
   * Set the users array.
   */
  private function users(array $users): void {
    $this->options["users"] = $users;
  }
  
  /**
   * Set the secure flag.
   */
  private function secure(bool $secure): void {
    $this->options["secure"] = $secure;
  }
  
  /**
   * Set hosts where secure rule is relaxed.
   */
  private function relaxed(array $relaxed): void {
    $this->options["relaxed"] = $relaxed;
  }
  
  /**
   * Set the handler which is called before other middlewares.
   */
  private function before(Closure $before): void {
    $this->options["before"] = $before->bindTo($this);
  }
  
  /**
   * Set the handler which is called after other middlewares.
   */
  private function after(Closure $after): void {
    $this->options["after"] = $after->bindTo($this);
  }
  
  /**
   * Set the handler which is if authentication fails.
   */
  private function error(callable $error): void {
    $this->options["error"] = $error;
  }
  
  /**
   * Set the rules
   */
  private function rules(array $rules) {
    $this->rules = $rules;
  }
  
  /**
   * Set the rules which determine if current request should be authenticated.
   *
   * Rules must be callables which return a boolean. If any of the rules return
   * boolean false current request will not be authenticated.
   *
   * @param array $rules
   */
  public function withRules(array $rules): self {
    $new = clone $this;
    /* Clear the stack */
    unset($new->rules);
    $new->rules = new \SplStack;
    
    /* Add the rules */
    foreach ($rules as $callable) {
      $new = $new->addRule($callable);
    }
    return $new;
  }
  
  /**
   * Add a rule to the rules stack.
   *
   * Rules must be callables which return a boolean. If any of the rules return
   * boolean false current request will not be authenticated.
   */
  public function addRule(callable $callable): self {
    $new = clone $this;
    $new->rules = clone $this->rules;
    $new->rules->push($callable);
    return $new;
  }
}

