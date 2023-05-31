<?php
declare(strict_types=1);

namespace acoby\tests\controller;

use acoby\exceptions\IllegalStateException;
use Fig\Http\Message\StatusCodeInterface;
use Slim\Psr7\Response;
use acoby\system\SessionManager;
use acoby\controller\AbstractEditController;
use acoby\services\AbstractFactory;

abstract class AbstractEditControllerTest extends AbstractFrontendControllerTest {
  /**
   * @param bool $save
   * @return object
   */
  protected abstract function createObject(bool $save = true) :object;

  /**
   * @param bool $new
   * @param object|null $object $object
   * @return string
   */
  protected abstract function getPath(bool $new = false, object $object = null) :string;
  
  /**
   * @param object|null $object
   * @return array
   */
  protected abstract function getArgs(object $object = null) :array;
  
  /**
   * @return AbstractEditController
   */
  protected abstract function getController() :AbstractEditController;
  
  /**
   *
   * @throws IllegalStateException
   */
  public function testView() {
    $admin = AbstractFactory::getUserService()->getUserByName("admin");
    $object = $this->createObject();
    SessionManager::getInstance()->unsetUser();
    
    $args = array();
    $request = $this->getRequest('GET', $this->getPath());
    $controller = $this->getController();

    $response = $controller->view($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());

    SessionManager::getInstance()->setUser($admin);
    
    $args = $this->getArgs($object);
    $request = $this->getRequest('GET',$this->getPath(false, $object));

    $response = $controller->view($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

    $response = $controller->edit($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

    $controller->setAttribute("action", "cancel");
    $response = $controller->edit($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

    $controller->setAttribute("action", "delete");
    $response = $controller->edit($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
  }
  
  protected abstract function fillForm(AbstractEditController $controller, object $object) :void;

  /**
   *
   * @throws IllegalStateException
   */
  public function testAdd() {
    $admin = AbstractFactory::getUserService()->getUserByName("admin");
    $object = $this->createObject(false);
    SessionManager::getInstance()->unsetUser();
    
    $args = array();
    $request = $this->getRequest('GET',$this->getPath(true));
    $controller = $this->getController();

    $response = $controller->add($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());

    SessionManager::getInstance()->setUser($admin);

    $response = $controller->add($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

    $controller->setAttribute("action", "cancel");
    $response = $controller->add($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

    $controller->setAttribute("action", "save");
    $this->fillForm($controller, $object);
    $response = $controller->add($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
  }
}