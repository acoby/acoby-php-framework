<?php
declare(strict_types=1);

namespace acoby;

use ErrorException;
use Throwable;
use Slim\Factory\AppFactory;
use Slim\App;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Fig\Http\Message\StatusCodeInterface;
use acoby\exceptions\IllegalStateException;
use acoby\services\ConfigService;
use acoby\middleware\RESTErrorHandler;
use acoby\middleware\OAuthAuthentication;
use acoby\system\HttpHeader;
use acoby\system\RequestUtils;
use acoby\middleware\VersionResponseHandler;

/**
 * A base class for a Slim REST Backend application.
 *
 * @author Thoralf Rickert-Wendt
 */
abstract class BackendApp {
  /** @var $app \Slim\App */
  protected $app;
  
  /**
   * @codeCoverageIgnore
   */
  protected function __construct() {
    set_error_handler(
      function ($severity, $message, $file, $line) {
        error_log($message." in ".$file." line ".$line);
        throw new ErrorException($message,$severity,$severity, $file, $line);
      }
    );
    $this->init();
  }
    
  /**
   * Runs the Slim App
   * @codeCoverageIgnore
   */
  public function run() :void {
    try {
      if ($this->app === null) throw new IllegalStateException("BackendApp not initialized");
      $this->app->run();
    } catch (HttpMethodNotAllowedException $exception) {
      $error = RequestUtils::createException(StatusCodeInterface::STATUS_BAD_REQUEST, "Could not handle method", $exception);
      
      http_response_code($error->code);
      header(HttpHeader::CONTENT_TYPE.": ".HttpHeader::MIMETYPE_JSON);
      echo json_encode($error);
    } catch (HttpNotFoundException $exception) {
      $error = RequestUtils::createException(StatusCodeInterface::STATUS_NOT_FOUND, "Could not find endpoint", $exception);
      
      http_response_code($error->code);
      header(HttpHeader::CONTENT_TYPE.": ".HttpHeader::MIMETYPE_JSON);
      echo json_encode($error);
      
    } catch (Throwable $exception) {
      $error = RequestUtils::createException(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, "Could not handle request", $exception);
      
      http_response_code($error->code);
      header(HttpHeader::CONTENT_TYPE.": ".HttpHeader::MIMETYPE_JSON);
      echo json_encode($error);
    }
  }
  
  /**
   * Is used by implementing classes to initialize all controllers
   * @param App $app
   */
  protected abstract function initController(App $app) :void;
  
  /**
   * Returns a list of middleware components
   * 
   * @param App $app
   * @return \Psr\Http\Server\MiddlewareInterface[]
   */
  protected abstract function getMiddleware(App $app) :array;
  
  /**
   * Returns the usefull OAuthConfiguration
   * @return array
   */
  protected abstract function getAppAuthSettings() :array;
  
  /**
   * @codeCoverageIgnore
   * 
   */
  protected function init() :void {
    $this->app = AppFactory::create();
    $this->app->add(new OAuthAuthentication($this->getAppAuthSettings()));
    $this->app->addMiddleware(new VersionResponseHandler());
    
    foreach ($this->getMiddleware($this-app) as $middleware) $this->app->addMiddleware($middleware);
    
    $errorMiddleware = $this->app->addErrorMiddleware((ConfigService::getString("acoby_environment") !== "prod"), true, true);
    $errorMiddleware->setDefaultErrorHandler([RESTErrorHandler::class,'handleError']);
    
    $this->initController($this->app);
    $this->app->addRoutingMiddleware();
  }
}