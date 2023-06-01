<?php
declare(strict_types=1);

namespace acoby\tests\controller;

use Fig\Http\Message\StatusCodeInterface;
use Slim\App;
use acoby\exceptions\IllegalArgumentException;
use acoby\services\UserService;
use acoby\models\AbstractSearch;
use acoby\models\AbstractUser;
use Psr\Http\Message\ServerRequestInterface;
use acoby\controller\AbstractRESTController;

abstract class AbstractRESTCRUDControllerTest extends AbstractBackendControllerTest {
  public const REQUEST_TYPE_CREATE = "CREATE";
  public const REQUEST_TYPE_GET    = "GET";
  public const REQUEST_TYPE_UPDATE = "UPDATE";
  public const REQUEST_TYPE_DELETE = "DELETE";
  public const REQUEST_TYPE_LIST   = "LIST";
  public const REQUEST_TYPE_SEARCH = "SEARCH";
  
  /**
   * Should initialize the app routes for this test
   * 
   * @param App $app
   */
  protected abstract function initRoutes(App $app) :void;

  /**
   * Returns a request object based on $requestType
   *
   * @param string $requestType
   * @param object|null $object
   * @return ServerRequestInterface
   */
  protected abstract function createRequest(string $requestType, object $object = null) :ServerRequestInterface;
  
  /**
   * Returns the App to test
   *
   * @return App
   */
  protected abstract function getApp() :App;
  
  /**
   * @return object
   */
  protected abstract function getObjectClass() :object;
  
  /**
   * @param object $object
   */
  protected abstract function verifyObject(object $object) :void;
  
  /**
   * @param object $source
   * @param object $copy
   */
  protected abstract function compareObject(object $source, object $copy) :void;
  
  /**
   * @param bool $save
   * @return object
   */
  protected abstract function createObject(bool $save = false) :object;

  /**
   * @param object|null $object $object
   * @return AbstractSearch
   */
  protected abstract function createSearch(object $object = null) :AbstractSearch;
  
  /**
   * @param string $role
   * @return AbstractUser
   */
  protected abstract function getUserForRole(string $role) :AbstractUser;
  
  /**
   * @return AbstractRESTController
   */
  protected abstract function getController() :AbstractRESTController;
  
  /**
   * Returns the minimum role for a specific rest type
   * @param string $requestType
   * @throws IllegalArgumentException
   * @return string
   */
  protected function getMinimumRole(string $requestType) :string {
    $controller = $this->getController();
    switch ($requestType) {
      case AbstractRESTCRUDControllerTest::REQUEST_TYPE_CREATE: return $controller->getCreateUserRole();
      case AbstractRESTCRUDControllerTest::REQUEST_TYPE_UPDATE: return $controller->getUpdateUserRole();
      case AbstractRESTCRUDControllerTest::REQUEST_TYPE_DELETE: return $controller->getDeleteUserRole();
      case AbstractRESTCRUDControllerTest::REQUEST_TYPE_SEARCH:
      case AbstractRESTCRUDControllerTest::REQUEST_TYPE_LIST:
      case AbstractRESTCRUDControllerTest::REQUEST_TYPE_GET: return $controller->getReadUserRole();
      default: throw new IllegalArgumentException("Unknown Request Type ".$requestType);
    }
  }

  /**
   *
   * @throws IllegalArgumentException
   * @throws IllegalArgumentException
   */
  public function testCREATE() {
    $app = $this->getApp();
    $this->initRoutes($app);
    
    $admin = $this->getUserForRole(UserService::ADMIN);
    $manager = $this->getUserForRole(UserService::MANAGER);
    $user = $this->getUserForRole(UserService::USER);
    $report = $this->getUserForRole(UserService::REPORT);
    
    $object = $this->createObject();
    
    // kein User angegeben, kein Object
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_CREATE);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

    $usersNotAllowed = array();
    $userAllowed = null;
    
    switch ($this->getMinimumRole(AbstractRESTCRUDControllerTest::REQUEST_TYPE_CREATE)) {
      case UserService::REPORT:
        $userAllowed = $report;
        break;
        
      case UserService::USER:
        $usersNotAllowed[] = $report;
        $userAllowed = $user;
        break;
        
      case UserService::MANAGER:
        $usersNotAllowed[] = $report;
        $usersNotAllowed[] = $user;
        $userAllowed = $manager;
        break;
        
      case UserService::ADMIN:
        $usersNotAllowed[] = $report;
        $usersNotAllowed[] = $user;
        $usersNotAllowed[] = $manager;
        $userAllowed = $admin;
        break;
    }
    
    foreach ($usersNotAllowed as $userNotAllowed) {
      // falscher User, Daten sind leer
      $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_CREATE);
      $request = $this->withUser($request, $userNotAllowed);
      $response = $app->handle($request);
      $this->assertEquals(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }
    
    // richtiger User, Daten sind leer
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_CREATE);
    $request = $this->withUser($request, $userAllowed);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_NOT_ACCEPTABLE, $response->getStatusCode());
    
    // richtiger User, Daten sind falsch
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_CREATE);
    $request = $this->withUser($request, $userAllowed);
    $request = $this->withBody($request, $this->getObjectClass());
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_NOT_ACCEPTABLE, $response->getStatusCode());
    
    // richtiger User, Daten sind korrekt
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_CREATE);
    $request = $this->withUser($request, $userAllowed);
    $request = $this->withBody($request, $object);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());
    
    $body = (string)$response->getBody();
    $this->assertGreaterThan(0, strlen($body));
    
    $result = $this->mapper->map($body, $this->getObjectClass());
    $this->compareObject($object, $result);
  }

  /**
   *
   * @throws IllegalArgumentException
   * @throws IllegalArgumentException
   */
  public function testUPDATE() {
    $app = $this->getApp();
    $this->initRoutes($app);
    
    $admin = $this->getUserForRole(UserService::ADMIN);
    $manager = $this->getUserForRole(UserService::MANAGER);
    $user = $this->getUserForRole(UserService::USER);
    $report = $this->getUserForRole(UserService::REPORT);
    
    $object = $this->createObject(true);
    
    // kein User angegeben, kein Object
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_UPDATE);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
    
    $usersNotAllowed = array();
    $userAllowed = null;
    
    switch ($this->getMinimumRole(AbstractRESTCRUDControllerTest::REQUEST_TYPE_UPDATE)) {
      case UserService::REPORT:
        $userAllowed = $report;
        break;
        
      case UserService::USER:
        $usersNotAllowed[] = $report;
        $userAllowed = $user;
        break;
        
      case UserService::MANAGER:
        $usersNotAllowed[] = $report;
        $usersNotAllowed[] = $user;
        $userAllowed = $manager;
        break;
        
      case UserService::ADMIN:
        $usersNotAllowed[] = $report;
        $usersNotAllowed[] = $user;
        $usersNotAllowed[] = $manager;
        $userAllowed = $admin;
        break;
    }
    
    foreach ($usersNotAllowed as $userNotAllowed) {
      // falscher User, Daten sind leer
      $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_UPDATE);
      $request = $this->withUser($request, $userNotAllowed);
      $response = $app->handle($request);
      $this->assertEquals(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }
    
    // richtiger User, Daten sind falsch
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_UPDATE);
    $request = $this->withUser($request, $userAllowed);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
    
    // richtiger User, Daten sind leer
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_UPDATE, $object);
    $request = $this->withUser($request, $userAllowed);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_NOT_ACCEPTABLE, $response->getStatusCode());
    
    // richtiger User, Daten sind falsch
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_UPDATE, $object);
    $request = $this->withUser($request, $userAllowed);
    $request = $this->withBody($request, $this->getObjectClass());
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_NOT_ACCEPTABLE, $response->getStatusCode());
    
    // richtiger User, Daten sind korrekt
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_UPDATE, $object);
    $request = $this->withUser($request, $userAllowed);
    $request = $this->withBody($request, $object);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());
    
    $body = (string)$response->getBody();
    $this->assertGreaterThan(0, strlen($body));
    
    $result = $this->mapper->map($body, $this->getObjectClass());
    $this->compareObject($object, $result);
  }

  /**
   *
   * @throws IllegalArgumentException
   * @throws IllegalArgumentException
   */
  public function testGET() {
    $app = $this->getApp();
    $this->initRoutes($app);
    
    $admin = $this->getUserForRole(UserService::ADMIN);
    $manager = $this->getUserForRole(UserService::MANAGER);
    $user = $this->getUserForRole(UserService::USER);
    $report = $this->getUserForRole(UserService::REPORT);
    
    $object = $this->createObject(true);
    
    // kein User angegeben, kein Object
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_GET);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
    
    $usersNotAllowed = array();
    $userAllowed = null;
    
    switch ($this->getMinimumRole(AbstractRESTCRUDControllerTest::REQUEST_TYPE_GET)) {
      case UserService::REPORT:
        $userAllowed = $report;
        break;
        
      case UserService::USER:
        $usersNotAllowed[] = $report;
        $userAllowed = $user;
        break;
        
      case UserService::MANAGER:
        $usersNotAllowed[] = $report;
        $usersNotAllowed[] = $user;
        $userAllowed = $manager;
        break;
        
      case UserService::ADMIN:
        $usersNotAllowed[] = $report;
        $usersNotAllowed[] = $user;
        $usersNotAllowed[] = $manager;
        $userAllowed = $admin;
        break;
    }
    
    foreach ($usersNotAllowed as $userNotAllowed) {
      // falscher User, Daten sind leer
      $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_GET);
      $request = $this->withUser($request, $userNotAllowed);
      $response = $app->handle($request);
      $this->assertEquals(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }
    
    // richtiger User, Daten sind falsch
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_GET);
    $request = $this->withUser($request, $userAllowed);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
    
    // richtiger User, Daten sind korrekt
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_GET, $object);
    $request = $this->withUser($request, $userAllowed);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    
    $body = (string)$response->getBody();
    $this->assertGreaterThan(0, strlen($body));
    
    $result = $this->mapper->map($body, $this->getObjectClass());
    $this->compareObject($object, $result);
  }

  /**
   *
   * @throws IllegalArgumentException
   */
  public function testDELETE() {
    $app = $this->getApp();
    $this->initRoutes($app);
    
    $admin = $this->getUserForRole(UserService::ADMIN);
    $manager = $this->getUserForRole(UserService::MANAGER);
    $user = $this->getUserForRole(UserService::USER);
    $report = $this->getUserForRole(UserService::REPORT);
    
    $object = $this->createObject(true);
    
    // kein User angegeben, kein Object
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_DELETE);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
    
    $usersNotAllowed = array();
    $userAllowed = null;
    
    switch ($this->getMinimumRole(AbstractRESTCRUDControllerTest::REQUEST_TYPE_DELETE)) {
      case UserService::REPORT:
        $userAllowed = $report;
        break;
        
      case UserService::USER:
        $usersNotAllowed[] = $report;
        $userAllowed = $user;
        break;
        
      case UserService::MANAGER:
        $usersNotAllowed[] = $report;
        $usersNotAllowed[] = $user;
        $userAllowed = $manager;
        break;
        
      case UserService::ADMIN:
        $usersNotAllowed[] = $report;
        $usersNotAllowed[] = $user;
        $usersNotAllowed[] = $manager;
        $userAllowed = $admin;
        break;
    }
    
    foreach ($usersNotAllowed as $userNotAllowed) {
      // falscher User, Daten sind leer
      $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_DELETE);
      $request = $this->withUser($request, $userNotAllowed);
      $response = $app->handle($request);
      $this->assertEquals(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }
    
    // richtiger User, Daten sind falsch
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_DELETE);
    $request = $this->withUser($request, $userAllowed);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
    
    // richtiger User, Daten sind korrekt
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_DELETE, $object);
    $request = $this->withUser($request, $userAllowed);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());
  }

  /**
   *
   * @throws IllegalArgumentException
   * @throws IllegalArgumentException
   */
  public function testLIST() {
    $app = $this->getApp();
    $this->initRoutes($app);
    
    $admin = $this->getUserForRole(UserService::ADMIN);
    $manager = $this->getUserForRole(UserService::MANAGER);
    $user = $this->getUserForRole(UserService::USER);
    $report = $this->getUserForRole(UserService::REPORT);
    
    $this->createObject(true);

    // kein User angegeben
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_LIST);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
    
    $usersNotAllowed = array();
    $userAllowed = null;
    
    switch ($this->getMinimumRole(AbstractRESTCRUDControllerTest::REQUEST_TYPE_LIST)) {
      case UserService::REPORT:
        $userAllowed = $report;
        break;
        
      case UserService::USER:
        $usersNotAllowed[] = $report;
        $userAllowed = $user;
        break;
        
      case UserService::MANAGER:
        $usersNotAllowed[] = $report;
        $usersNotAllowed[] = $user;
        $userAllowed = $manager;
        break;
        
      case UserService::ADMIN:
        $usersNotAllowed[] = $report;
        $usersNotAllowed[] = $user;
        $usersNotAllowed[] = $manager;
        $userAllowed = $admin;
        break;
    }
    
    foreach ($usersNotAllowed as $userNotAllowed) {
      // falscher User, Daten sind leer
      $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_LIST);
      $request = $this->withUser($request, $userNotAllowed);
      $response = $app->handle($request);
      $this->assertEquals(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }

    // richtiger User
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_LIST);
    $request = $this->withUser($request, $userAllowed);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

    $body = (string)$response->getBody();
    $this->assertGreaterThan(0, strlen($body));

    $elements = $this->mapper->mapList($body, get_class($this->getObjectClass()));
    $this->assertGreaterThan(0, count($elements));
    
    foreach ($elements as $element) {
      $this->verifyObject($element);
    }
  }

  /**
   *
   * @throws IllegalArgumentException
   * @throws IllegalArgumentException
   */
  public function testSEARCH() {
    $app = $this->getApp();
    $this->initRoutes($app);
    
    $admin = $this->getUserForRole(UserService::ADMIN);
    $manager = $this->getUserForRole(UserService::MANAGER);
    $user = $this->getUserForRole(UserService::USER);
    $report = $this->getUserForRole(UserService::REPORT);
    
    $object = $this->createObject(true);
    $emptySearch = $this->createSearch();
    $fullSearch = $this->createSearch($object);
    
    // kein User angegeben
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_SEARCH);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
    
    $usersNotAllowed = array();
    $userAllowed = null;
    
    switch ($this->getMinimumRole(AbstractRESTCRUDControllerTest::REQUEST_TYPE_SEARCH)) {
      case UserService::REPORT:
        $userAllowed = $report;
        break;
        
      case UserService::USER:
        $usersNotAllowed[] = $report;
        $userAllowed = $user;
        break;
        
      case UserService::MANAGER:
        $usersNotAllowed[] = $report;
        $usersNotAllowed[] = $user;
        $userAllowed = $manager;
        break;
        
      case UserService::ADMIN:
        $usersNotAllowed[] = $report;
        $usersNotAllowed[] = $user;
        $usersNotAllowed[] = $manager;
        $userAllowed = $admin;
        break;
    }
    
    foreach ($usersNotAllowed as $userNotAllowed) {
      // falscher User, Daten sind leer
      $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_SEARCH);
      $request = $this->withUser($request, $userNotAllowed);
      $response = $app->handle($request);
      $this->assertEquals(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }
    
    // richtiger User, Daten sind leer
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_SEARCH);
    $request = $this->withUser($request, $userAllowed);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_NOT_ACCEPTABLE, $response->getStatusCode());
    
    // richtiger User, Daten sind korrekt leer
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_SEARCH);
    $request = $this->withUser($request, $userAllowed);
    $request = $this->withBody($request, $emptySearch);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    
    $body = (string)$response->getBody();
    $this->assertGreaterThan(0, strlen($body));
    
    $elements = json_decode($body);
    $this->assertGreaterThan(0, count($elements));
    
    // richtiger User, Daten sind korrekt leer
    $request = $this->createRequest(AbstractRESTCRUDControllerTest::REQUEST_TYPE_SEARCH);
    $request = $this->withUser($request, $userAllowed);
    $request = $this->withBody($request, $fullSearch);
    $response = $app->handle($request);
    $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    
    $body = (string)$response->getBody();
    $this->assertGreaterThan(0, strlen($body));
    
    $elements = $this->mapper->mapList($body, get_class($this->getObjectClass()));
    $this->assertGreaterThan(0, count($elements));
    
    foreach ($elements as $element) {
      $this->verifyObject($element);
    }
  }
}
