<?php
declare(strict_types=1);

namespace acoby;

use ErrorException;
use Throwable;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\ResponseEmitter;
use acoby\exceptions\IllegalStateException;
use acoby\middleware\AcobyAuthHandler;
use acoby\services\ConfigService;
use acoby\middleware\HTMLErrorHandler;
use acoby\system\SessionManager;
use acoby\system\HttpHeader;
use acoby\models\AbstractUser;

/**
 * A base class for a Slim/Twig Frontend application.
 * 
 * @author Thoralf Rickert-Wendt
 */
abstract class FrontendApp {
  /** @var $app App */
  protected $app;
  /** @var $twig Twig */
  protected $twig;

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
      if ($this->app === null) throw new IllegalStateException("FrontendApp not initialized");
      $this->app->run();
    } catch (Throwable $exception) {
      $data = $this->handleError($exception);
      
      $user = SessionManager::getInstance()->getUser($this->getUserObject());
      $responseFactory = new ResponseFactory();
      $response = $responseFactory->createResponse($data["code"]);
      
      if ($user !== null) {
        $response = $this->twig->render($response->withHeader(HttpHeader::CONTENT_TYPE,HttpHeader::MIMETYPE_HTML), 'error.html', $data);
      } else {
        $response = $this->twig->render($response->withHeader(HttpHeader::CONTENT_TYPE,HttpHeader::MIMETYPE_HTML), 'login/error.html', $data);
      }
      
      $responseEmitter = new ResponseEmitter();
      $responseEmitter->emit($response);
    }
  }
  
  /**
   * Returns a new User object for session manager
   * @return AbstractUser
   */
  protected abstract function getUserObject() :AbstractUser;
  
  /**
   * Returns an error suitable for rendering a useful error message on the screen
   */
  protected abstract function handleError(Throwable $throwable) :array;
  
  /**
   * Is used by implementing classes to initialize all controllers
   * @param App $app
   */
  protected abstract function initController(App $app) :void;
  
  /**
   * Returns a list of 
   * @param App $app
   * @return \Twig\Extension\ExtensionInterface[]
   */
  protected abstract function getTwigExtensions(App $app) :array;
  
  /**
   * Returns the location of the templates
   */
  protected abstract function getTemplatePath() :string;
  
  protected abstract function handleCookies() :void;
  
  /**
   *
   */
  protected function init() :void {
    AcobyAuthHandler::addIgnoreRoute("login");
    AcobyAuthHandler::addIgnoreRoute("status");
    
    $this->app = AppFactory::create();
    $this->twig = Twig::create($this->getTemplatePath(), ['cache' => false]);
    
    foreach ($this->getTwigExtensions($this->app) as $extension) $this->twig->addExtension($extension);
    
    $this->app->add(TwigMiddleware::create($this->app,$this->twig));
    $this->app->add(AcobyAuthHandler::class.':handleRequest');
    
    $errorMiddleware = $this->app->addErrorMiddleware((ConfigService::getString("acoby_environment") !== "prod"), true, true);
    $errorMiddleware->setDefaultErrorHandler([HTMLErrorHandler::class,'handleError']);
    
    $this->initController($this->app);
    $this->app->addRoutingMiddleware();
    
    $this->handleCookies();
  }
  
}