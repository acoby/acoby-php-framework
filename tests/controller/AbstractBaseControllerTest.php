<?php
declare(strict_types=1);

namespace acoby\controller;

use Slim\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use acoby\services\ConfigService;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\RequestFactory;
use acoby\BaseTestCase;
use acoby\models\AbstractUser;

abstract class AbstractBaseControllerTest extends BaseTestCase {
  public abstract function testCREATE();
  public abstract function testGET();
  public abstract function testLIST();
  public abstract function testUPDATE();
  public abstract function testDELETE();
  public abstract function testSEARCH();
  
  protected abstract function getController() :AbstractController;

  /**
   * Returrns a new request object
   * 
   * @param string $method
   * @param string $uri
   * @return RequestInterface
   */
  protected function getRequest(string $method, string $uri) :RequestInterface {
    return (new RequestFactory())->createRequest($method, $uri);
  }
  
  /**
   * Returns a new reponse object
   * 
   * @return ResponseInterface
   */
  protected function getResponse() :ResponseInterface {
    return (new ResponseFactory())->createResponse();
  }
  
  /**
   * Returns a JSON respresentation of the body
   * 
   * @param ResponseInterface $body
   * @return array
   */
  protected function getJSONResponseArray(ResponseInterface $response) :array {
    $body = $response->getBody()->__toString();
    return $this->mapper->decode($body);
  }
  
  /**
   * Returns a JSON respresentation of the body
   * 
   * @param ResponseInterface $response
   * @param string $class
   * @return object
   */
  protected function getJSONResponseObject(ResponseInterface $response, string $class) :object {
    $body = $response->getBody()->__toString();
    return $this->mapper->map($body, new $class);
  }
  
  /**
   * Returns a JSON respresentation of the body
   * 
   * @param ResponseInterface $response
   * @param string $class
   * @return object
   */
  protected function getJSONResponseObjectList(ResponseInterface $response, string $class) :object {
    $body = $response->getBody()->__toString();
    return $this->mapper->mapList($body, $class);
  }
  
  /**
   * 
   * @param Request $request
   * @param object $object
   * @return Request
   */
  protected function withBody(Request $request, $object) :Request {
    $body = $request->getBody();
    $body->rewind();
    $body->write(json_encode($object));
    return $request->withBody($body);
  }
  
  /**
   * 
   * @param Request $request
   * @param AbstractUser $user
   * @param string $password
   * @return Request
   */
  protected function withUser(Request $request, AbstractUser $user, string $password = null) :Request {
    $data = $user->username.":";
    if ($password !== null) {
      $data.= $password;
    } else if ($user->username === "user") {
      $data.= "User!Password";
    } else if ($user->username === "admin") {
      $data.= ConfigService::getString("acoby_admin_password");
    }
    $basic = "Basic ".base64_encode($data);
    return $request->withHeader("Authorization", $basic);
  }
}
