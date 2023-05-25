<?php
declare(strict_types=1);

namespace acoby\tests\controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\RequestFactory;

use acoby\services\ConfigService;
use acoby\tests\BaseTestCase;
use acoby\models\AbstractUser;

abstract class AbstractBackendControllerTest extends BaseTestCase {
  /**
   * Creates a new Request
   * 
   * @param string $method
   * @param string $uri
   * @return RequestInterface
   */
  protected function getRequest(string $method, string $uri) :RequestInterface {
    return (new RequestFactory())->createRequest($method, $uri);
  }
  
  /**
   * Creates a new Response
   * 
   * @return ResponseInterface
   */
  protected function getResponse() :ResponseInterface {
    return (new ResponseFactory())->createResponse();
  }
  
  /**
   * Returns a JSON Response
   * 
   * @param string $body
   * @param string $class
   * @param bool $asList
   * @return array
   */
  protected function getJSONResponse(string $body, string $class = null, bool $asList = false) :array {
    if ($class === null) {
      return $this->mapper->decode($body);
    } else if ($asList) {
      return $this->mapper->mapList($body, $class);
    } else {
      return $this->mapper->map($body, new $class);
    }
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
   * Adds a Body to a Request
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
   * Adds a User to a Request
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
    } else if ($user->username === "report") {
      $data.= "User!Password";
    } else if ($user->username === "user") {
      $data.= "User!Password";
    } else if ($user->username === "manager") {
      $data.= "User!Password";
    } else if ($user->username === "admin") {
      $data.= ConfigService::getString("acoby_admin_password");
    }
    $basic = "Basic ".base64_encode($data);
    return $request->withHeader("Authorization", $basic);
  }
}
