<?php
declare(strict_types=1);

namespace acoby\tests\controller;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;
use Slim\Psr7\Headers;
use Slim\Views\Twig;
use acoby\system\RequestBody;
use acoby\services\ConfigService;
use acoby\tests\BaseTestCase;
use acoby\system\AcobyTwigExtension;

abstract class AbstractFrontendControllerTest extends BaseTestCase {
  private $twig;
  
  /**
   * {@inheritDoc}
   * @see \acoby\tests\BaseTestCase::setUp()
   */
  public function setUp() :void {
    parent::setUp();

    $this->twig = Twig::create(ConfigService::getString("basedir").'/templates', ['cache' => false]);
    $this->twig->addExtension(new AcobyTwigExtension());
  }
  
  /**
   * Creates a Response
   * @return ResponseInterface
   */
  protected function getResponse() :ResponseInterface {
    return (new ResponseFactory())->createResponse();
  }
  
  /**
   * Creates a Request
   * @param string $method
   * @param string $path
   * @return \Slim\Psr7\Request
   */
  protected function getRequest(string $method, string $path) {
    return $this->getRequestWithBody($method, $path);
  }

  /**
   * Creates a Request with a Body
   * @param string $method
   * @param string $path
   * @param string $object
   * @return \Slim\Psr7\Request
   */
  protected function getRequestWithBody(string $method, string $path, string $object = null) {
    $uri = new Uri("http", "localhost", 80, $path);
    $headers = new Headers(array());
    $cookies = array();
    $serverParams = $_SERVER;
    $body = new RequestBody();
    if ($object !== null) $body->write(json_encode($object));
    $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);
    return $request->withAttribute("view", $this->twig);
  }
}
