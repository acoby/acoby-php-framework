<?php
namespace acoby\middleware;

use Exception;
use Fig\Http\Message\StatusCodeInterface;
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
  
  public static function addIgnoreRoute(string $route) :void {
    AcobyAuthHandler::$ignoreRoute[] = $route;
  }
  
  public static function addCookieSupport(string $cookieName, string $function) :void {
    AcobyAuthHandler::$cookieSupport[$cookieName] = $function;
  }

  /**
   * @throws ObjectNotFoundException
   */
  public function handleRequest($request, $handler) {
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
}