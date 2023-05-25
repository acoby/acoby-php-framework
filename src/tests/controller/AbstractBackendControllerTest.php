<?php
declare(strict_types=1);

namespace acoby\tests\controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\RequestFactory;

use acoby\services\ConfigService;
use acoby\controller\AbstractController;
use acoby\tests\BaseTestCase;
use acoby\models\AbstractUser;

abstract class AbstractBackendControllerTest extends BaseTestCase {
  public function setUp() :void {
    parent::setUp();
  }
  
  public abstract function testCREATE();
  public abstract function testGET();
  public abstract function testLIST();
  public abstract function testUPDATE();
  public abstract function testDELETE();
  public abstract function testSEARCH();
  
  protected abstract function getController() :AbstractController;
  
  protected abstract function getApp() :App;
  
  protected function getRequest(string $method, string $uri) :RequestInterface {
    return (new RequestFactory())->createRequest($method, $uri);
  }
  
  protected function getResponse() :ResponseInterface {
    return (new ResponseFactory())->createResponse();
  }
  
  protected function getJSONResponse(string $body, string $class = null, bool $asList = false) :array {
    if ($class === null) {
      return $this->mapper->decode($body);
    } else if ($asList) {
      return $this->mapper->mapList($body, $class);
    } else {
      return $this->mapper->map($body, new $class);
    }
  }
  
  protected function withBody(Request $request, $object) :Request {
    $body = $request->getBody();
    $body->rewind();
    $body->write(json_encode($object));
    return $request->withBody($body);
  }
  
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
