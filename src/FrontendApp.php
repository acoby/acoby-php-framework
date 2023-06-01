<?php
declare(strict_types=1);

namespace acoby;

use acoby\exceptions\IllegalArgumentException;
use acoby\system\Utils;
use ErrorException;
use Exception;
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
use Twig\Error\LoaderError;
use Twig\Extension\ExtensionInterface;

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
   * @throws ErrorException|LoaderError
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
   * @throws IllegalArgumentException
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

      try {
        if ($user !== null) {
          $response = $this->twig->render($response->withHeader(HttpHeader::CONTENT_TYPE,HttpHeader::MIMETYPE_HTML), 'error.html', $data);
        } else {
          $response = $this->twig->render($response->withHeader(HttpHeader::CONTENT_TYPE,HttpHeader::MIMETYPE_HTML), 'login/error.html', $data);
        }
      } catch (Exception $exception) {
        Utils::logException("Could not render error template.",$exception);
        echo "Fatal. Problem during rendering error message. More details in Log.";
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
   * @return ExtensionInterface[]
   */
  protected abstract function getTwigExtensions(App $app) :array;
  
  /**
   * Returns the location of the templates
   */
  protected abstract function getTemplatePath() :string;
  
  protected abstract function handleCookies() :void;
  
  /**
   *
   * @throws LoaderError
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
  
  /**
   * @return App
   */
  public function getApp() :App {
    return $this->app;
  }
  
  /**
   * @return Twig
   */
  public function getTwig() :Twig {
    return $this->twig;
  }
}