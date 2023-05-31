<?php
namespace acoby\middleware;

use acoby\exceptions\IllegalArgumentException;
use acoby\exceptions\IllegalStateException;
use acoby\models\AbstractUser;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteContext;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use acoby\exceptions\ObjectNotFoundException;
use acoby\system\SessionManager;
use acoby\services\ConfigService;
use acoby\system\Utils;
use acoby\services\AbstractFactory;
use acoby\system\RequestUtils;
use League\OAuth2\Client\Token\AccessToken;

class AcobyAuthHandler {
  private static $ignoreRoute = array();
  private static $cookieSupport = array();
  private static $options = array();

  /**
   * Adds a path to the ignore auth list (do not make any auth checks)
   *
   * @param string $route
   */
  public static function addIgnoreRoute(string $route) :void {
    AcobyAuthHandler::$ignoreRoute[] = $route;
  }

  /**
   * Handle cookies automatically to create session
   *
   * @param string $cookieName
   * @param string $function
   */
  public static function addCookieSupport(string $cookieName, string $function) :void {
    AcobyAuthHandler::$cookieSupport[$cookieName] = $function;
  }

  /**
   * @param string $key
   * @param string $value
   */
  public static function setOption(string $key, string $value) :void {
    AcobyAuthHandler::$options[$key] = $value;
  }

  /**
   * @param ServerRequestInterface $request
   * @param $handler
   * @return ResponseInterface
   * @throws ObjectNotFoundException
   */
  public function handleRequest(ServerRequestInterface $request, $handler) :ResponseInterface {
    $routeContext = RouteContext::fromRequest($request);
    $route = $routeContext->getRoute();
    if ($route === null) throw new ObjectNotFoundException();
    
    foreach (AcobyAuthHandler::$cookieSupport as $cookieName => $function) {
      if (isset($_COOKIE[$cookieName])) {
        call_user_func($function, $request);
      }
    }
    
    if (ConfigService::getBool("acoby_oidc_enabled",false)) {
      $provider = new Keycloak([
          'authServerUrl'         => ConfigService::getString("acoby_oidc_provider_url"),
          'realm'                 => ConfigService::getString("acoby_oidc_realm"),
          'clientId'              => ConfigService::getString("acoby_oidc_client_id"),
          'clientSecret'          => ConfigService::getString("acoby_oidc_client_secret"),
          'redirectUri'           => ConfigService::getString("acoby_oidc_callback_url"),
      ]);
      
      if (!SessionManager::getInstance()->contains(SessionManager::SESSION_KEY_ACCESS_TOKEN)) {
        $code = RequestUtils::getStringQueryParameter($request, "code");
        $state = RequestUtils::getStringQueryParameter($request, "state");
        
        if ($code === null || $state === null) {
          // If we don't have an authorization code then get one
          $authUrl = $provider->getAuthorizationUrl();
          SessionManager::getInstance()->set(SessionManager::SESSION_KEY_OAUTH2STATE, $provider->getState());
          SessionManager::getInstance()->set(SessionManager::SESSION_KEY_REDIRECT, $_SERVER["REQUEST_URI"]);
          
          $responseFactory = new ResponseFactory();
          return $responseFactory->createResponse(StatusCodeInterface::STATUS_MOVED_PERMANENTLY)->withHeader("Location", $authUrl);
        } else {
          if ($state !== SessionManager::getInstance()->get(SessionManager::SESSION_KEY_OAUTH2STATE)) {
            // Check given state against previously stored one to mitigate CSRF attack
            SessionManager::getInstance()->unset(SessionManager::SESSION_KEY_OAUTH2STATE);
            Utils::logError('Invalid state, make sure HTTP sessions are enabled.');
            
          } else {
            try {
              // Try to get an access token (using the authorization coe grant)
              $token = $provider->getAccessToken('authorization_code', ['code' => $code]);
              
              SessionManager::getInstance()->set(SessionManager::SESSION_KEY_REFRESH_TOKEN, $token->getRefreshToken());
              SessionManager::getInstance()->set(SessionManager::SESSION_KEY_ACCESS_TOKEN, $token->getToken());
              SessionManager::getInstance()->set(SessionManager::SESSION_KEY_EXPIRES, "".$token->getExpires());
              
              // We got an access token, let's now get the user's details
              $ssoUser = $provider->getResourceOwner($token);
              $user = AbstractFactory::getUserService()->getUserByEMail($ssoUser->getEmail());
              if ($user === null && isset(AcobyAuthHandler::$options["sso_auto_insert"])) {
                $user = $this->createSSOUser($token->getToken());
              }
              if ($user !== null) {
                SessionManager::getInstance()->setUser($user);
              } else {
                Utils::logError("Failed to set access token");
                SessionManager::getInstance()->unset(SessionManager::SESSION_KEY_ACCESS_TOKEN);
              }
            } catch (Exception $e) {
              Utils::logException("Failed to get access token",$e);
              SessionManager::getInstance()->unset(SessionManager::SESSION_KEY_ACCESS_TOKEN);
            }
          }
        }
      } else {
        $token = new AccessToken([
            "access_token"  => SessionManager::getInstance()->get(SessionManager::SESSION_KEY_ACCESS_TOKEN),
            "refresh_token" => SessionManager::getInstance()->get(SessionManager::SESSION_KEY_REFRESH_TOKEN),
            "expires" => intval(SessionManager::getInstance()->get(SessionManager::SESSION_KEY_EXPIRES))
        ]);
        if ($token->hasExpired()) {
          try {
            $newToken = $provider->getAccessToken("refresh_token",["refresh_token"=>$token->getRefreshToken()]);

            SessionManager::getInstance()->set(SessionManager::SESSION_KEY_REFRESH_TOKEN, $newToken->getRefreshToken());
            SessionManager::getInstance()->set(SessionManager::SESSION_KEY_ACCESS_TOKEN, $newToken->getToken());
            SessionManager::getInstance()->set(SessionManager::SESSION_KEY_EXPIRES, "".$newToken->getExpires());
          } catch (Exception $e) {
            Utils::logException("Failed to refresh access token",$e);
            SessionManager::getInstance()->unset(SessionManager::SESSION_KEY_ACCESS_TOKEN);
          }
        }
      }
    }
    
    if (!SessionManager::getInstance()->contains(SessionManager::SESSION_KEY_USER)) {
      $routeName = $route->getName();
      if (in_array($routeName, AcobyAuthHandler::$ignoreRoute)) {
        return $handler->handle($request);
      } else {
        $routeParser = $routeContext->getRouteParser();
        $url = $routeParser->urlFor('login');
        
        $redirect = $_SERVER["REQUEST_URI"];
        SessionManager::getInstance()->set(SessionManager::SESSION_KEY_REDIRECT, $redirect);
        
        $responseFactory = new ResponseFactory();
        return $responseFactory->createResponse(StatusCodeInterface::STATUS_MOVED_PERMANENTLY)->withHeader("Location", $url);
      }
    }
    return $handler->handle($request);
  }

  /**
   * @throws IllegalStateException
   * @throws IllegalArgumentException
   */
  protected function createSSOUser(string $token) :?AbstractUser {
    if (!isset(AcobyAuthHandler::$options["sso_pubkey"])) throw new IllegalStateException("Missing option 'sso_pubkey' to validate OIDC tokens.");
    if (!isset(AcobyAuthHandler::$options["sso_algorithm"])) throw new IllegalStateException("Missing option 'sso_algorithm' to validate OIDC tokens.");
    $params = JWTHandler::validateToken($token, AcobyAuthHandler::$options["sso_pubkey"], AcobyAuthHandler::$options["sso_algorithm"]);
    if (!$params->valid) throw new IllegalArgumentException("SSO Token not valid");

    if (!isset(AcobyAuthHandler::$options["sso_create"])) throw new IllegalStateException("Missing option 'sso_create' to create OIDC user.");
    return call_user_func(AcobyAuthHandler::$options["sso_create"], $params);
  }
}