<?php
declare(strict_types=1);

namespace acoby\tests\controller;

use Fig\Http\Message\StatusCodeInterface;
use Slim\Psr7\Response;
use acoby\system\SessionManager;
use acoby\controller\AbstractListController;
use acoby\services\AbstractFactory;

abstract class AbstractListControllerTest extends AbstractFrontendControllerTest {
  /**
   * @param bool $api
   * @return string
   */
  protected abstract function getPath(bool $api = false) :string;

  /**
   * @return AbstractListController
   */
  protected abstract function getController() :AbstractListController;
  
  /**
   * 
   */
  public function testHTMLView() {
    $admin = AbstractFactory::getUserService()->getUserByName("admin");
    SessionManager::getInstance()->unsetUser();
    
    $args = array();
    $request = $this->getRequest('GET',$this->getPath());
    $controller = $this->getController();

    $response = $controller->view($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());

    SessionManager::getInstance()->setUser($admin);

    $response = $controller->view($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
  }
  
  /**
   * 
   */
  public function testJSONList() {
    $admin = AbstractFactory::getUserService()->getUserByName("admin");
    SessionManager::getInstance()->unsetUser();
    
    $args = array();
    $request = $this->getRequest('GET',$this->getPath(true));
    $controller = $this->getController();
    
    $response = $controller->list($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    
    SessionManager::getInstance()->setUser($admin);
    
    $response = $controller->list($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
  }
  
  public function testJSONValues() {
    $admin = AbstractFactory::getUserService()->getUserByName("admin");
    SessionManager::getInstance()->unsetUser();
    
    $args = array();
    $request = $this->getRequest('GET',$this->getPath(true));
    $controller = $this->getController();
    
    $response = $controller->list($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    
    SessionManager::getInstance()->setUser($admin);
    
    $response = $controller->values($request, new Response(), $args);
    $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
  }
}