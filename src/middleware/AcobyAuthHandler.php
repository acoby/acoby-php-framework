<?php
namespace acoby\middleware;

use Fig\Http\Message\StatusCodeInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteContext;
use acoby\exceptions\ObjectNotFoundException;
use acoby\system\SessionManager;

class AcobyAuthHandler {
  private static $ignoreRoute = array();
  private static $cookieSupport = array();
  
  public static function addIgnoreRoute(string $route) :void {
    AcobyAuthHandler::$ignoreRoute[] = $route;
  }
  
  public static function addCookieSupport(string $cookieName, string $function) :void {
    AcobyAuthHandler::$cookieSupport[$cookieName] = $function;
  }
  
  public function handleRequest($request, $handler) {
    $routeContext = RouteContext::fromRequest($request);
    $route = $routeContext->getRoute();
    if ($route === null) throw new ObjectNotFoundException();
    
    foreach (AcobyAuthHandler::$cookieSupport as $cookieName => $function) {
      if (isset($_COOKIE[$cookieName])) {
        call_user_func($function, $request);
      }
    }

    if (!isset($_SESSION[SessionManager::SESSION_KEY_USER])) {
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