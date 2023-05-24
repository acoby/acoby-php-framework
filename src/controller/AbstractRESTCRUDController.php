<?php
declare(strict_types=1);

namespace acoby\controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;
use acoby\models\AbstractSearch;
use acoby\exceptions\IllegalArgumentException;
use acoby\exceptions\AccessDeniedException;
use Fig\Http\Message\StatusCodeInterface;
use acoby\exceptions\ObjectNotFoundException;
use acoby\models\AbstractUser;
use acoby\services\UserService;
use acoby\system\RequestUtils;

/**
 * A base class for CRUD REST controllers which are stateless and contains the operations of RestCntroller
 * 
 * - create
 * - read
 * - update
 * - delete
 * 
 * Also we have a search interface
 * 
 * @author Thoralf Rickert-Wendt
 */
abstract class AbstractRESTCRUDController extends AbstractRESTController implements RestController {
  /**
   * @return object
   */
  protected abstract function getNewObject() :object;

  /**
   * @return object
   */
  protected abstract function getNewSearchObject() :AbstractSearch;
  
  /**
   * @param AbstractSearch $search
   * @param AbstractUser $user
   * @return object[]
   */
  protected abstract function searchObjects(ServerRequestInterface $request, array $args, AbstractSearch $search, AbstractUser $user) :array;
  
  /**
   * @param object $object
   * @param AbstractUser $user
   * @return object|NULL
   * @throws IllegalArgumentException
   */
  protected abstract function createObject(ServerRequestInterface $request, array $args, object $object, AbstractUser $user) :?object;

  /**
   * @param object $object
   * @param AbstractUser $user
   * @return object|NULL
   * @throws IllegalArgumentException
   */
  protected abstract function updateObject(ServerRequestInterface $request, array $args, object $object, AbstractUser $user) :?object;
  
  /**
   * @param object $object
   * @param AbstractUser $user
   * @return bool
   */
  protected abstract function deleteObject(ServerRequestInterface $request, array $args, object $object, AbstractUser $user) :bool;
  
  /**
   * @param ServerRequestInterface $request
   * @param array $args
   * @param AbstractUser $user
   * @return object|NULL
   * @throws IllegalArgumentException
   */
  protected abstract function getObject(ServerRequestInterface $request, array $args, AbstractUser $user) :?object;
  
  /**
   * @param bool $expand
   * @param int $offset
   * @param int $limit
   * @param AbstractUser $user
   * @return array
   */
  protected abstract function getObjects(ServerRequestInterface $request, array $args, bool $expand, int $offset, int $limit, AbstractUser $user) :array;

  /**
   * @param object $oldObject
   * @param object $newObject
   * @param AbstractUser $user
   * @throws IllegalArgumentException
   */
  protected function compareObject(object $oldObject, object $newObject, AbstractUser $user) :void {
    if ($oldObject->externalId !== $newObject->externalId) throw new IllegalArgumentException("Different object defined");
  }

  /**
   * @return string
   * @codeCoverageIgnore
   */
  public function getCreateUserRole() :string {
    return UserService::USER;
  }
  
  /**
   * @return string
   */
  public function getReadUserRole() :string {
    return UserService::USER;
  }

  /**
   * @return string
   * @codeCoverageIgnore
   */
  public function getUpdateUserRole() :string {
    return UserService::USER;
  }
  
  /**
   * @return string
   * @codeCoverageIgnore
   */
  public function getDeleteUserRole() :string {
    return UserService::USER;
  }
  
  /**
   * {@inheritDoc}
   * @see \acoby\controller\RestController::create()
   */
  public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
    try {
      $user = $this->getRequestUser($request, $this->getCreateUserRole());
      $object = $this->mapper->map($request->getBody()->__toString(), $this->getNewObject());
      
      $newObject = $this->createObject($request, $args, $object, $user);
      if ($newObject === null) throw new IllegalArgumentException('Invalid input');
      return $this->withJSONObject($response, $newObject, StatusCodeInterface::STATUS_CREATED);
    } catch (AccessDeniedException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_FORBIDDEN);
    } catch (ObjectNotFoundException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_NOT_FOUND);
    } catch (IllegalArgumentException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_NOT_ACCEPTABLE);
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      return $this->withJSONException($response, $exception);
      // @codeCoverageIgnoreEnd
    }
  }

  /**
   * {@inheritDoc}
   * @see \acoby\controller\RestController::get()
   */
  public function get(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
    try {
      $user = $this->getRequestUser($request, $this->getReadUserRole());
      $object = $this->getObject($request, $args, $user);
      if ($object === null) throw new ObjectNotFoundException('Invalid input');
      return $this->withJSONObject($response, $object, StatusCodeInterface::STATUS_OK);
    } catch (AccessDeniedException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_FORBIDDEN);
    } catch (ObjectNotFoundException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_NOT_FOUND);
    } catch (IllegalArgumentException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_NOT_ACCEPTABLE);
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      return $this->withJSONException($response, $exception);
      // @codeCoverageIgnoreEnd
    }
  }
  
  /**
   * {@inheritDoc}
   * @see \acoby\controller\RestController::update()
   */
  public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
    try {
      $user = $this->getRequestUser($request, $this->getUpdateUserRole());
      $oldObject = $this->getObject($request, $args, $user);
      if ($oldObject === null) throw new ObjectNotFoundException('Invalid input');

      $object = $this->mapper->map($request->getBody()->__toString(), $this->getNewObject());
      $this->compareObject($oldObject,$object,$user);
      
      $newObject = $this->updateObject($request, $args, $object, $user);
      if ($newObject === null) throw new IllegalArgumentException('Invalid input');
      
      return $this->withJSONObject($response, $newObject, StatusCodeInterface::STATUS_ACCEPTED);
    } catch (AccessDeniedException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_FORBIDDEN);
    } catch (ObjectNotFoundException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_NOT_FOUND);
    } catch (IllegalArgumentException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_NOT_ACCEPTABLE);
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      return $this->withJSONException($response, $exception);
      // @codeCoverageIgnoreEnd
    }
  }
  
  /**
   * {@inheritDoc}
   * @see \acoby\controller\RestController::delete()
   */
  public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
    try {
      $user = $this->getRequestUser($request, $this->getDeleteUserRole());
      $object = $this->getObject($request, $args, $user);
      if ($object === null) throw new ObjectNotFoundException('Invalid input');
      
      $state = $this->deleteObject($request, $args, $object,$user);
      if (!$state) {
        // @codeCoverageIgnoreStart
        throw new IllegalArgumentException('Object could not be deleted');
        // @codeCoverageIgnoreEnd
      }
      return $this->withJSONObject($response, RequestUtils::createResult(StatusCodeInterface::STATUS_ACCEPTED, 'Object deleted'), StatusCodeInterface::STATUS_ACCEPTED);
    } catch (AccessDeniedException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_FORBIDDEN);
    } catch (ObjectNotFoundException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_NOT_FOUND);
    } catch (IllegalArgumentException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_NOT_ACCEPTABLE);
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      return $this->withJSONException($response, $exception);
      // @codeCoverageIgnoreEnd
    }
  }
  
  /**
   * {@inheritDoc}
   * @see \acoby\controller\RestController::search()
   */
  public function search(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
    try {
      $user = $this->getRequestUser($request, $this->getReadUserRole());

      /** @var $search AbstractSearch */
      $search = $this->mapper->map($request->getBody()->__toString(), $this->getNewSearchObject());
      if ($search === null) throw new IllegalArgumentException('Search could not be parsed');
      $search->verify();
      // add one to find more then expected (to see, if there are more objects available)
      $search->limit = $search->limit+1; 

      $objects = $this->searchObjects($request, $args, $search,$user);

      return $this->withJSONListResponse($response, $objects, $search->offset, $search->limit-1);      
    } catch (AccessDeniedException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_FORBIDDEN);
    } catch (ObjectNotFoundException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_NOT_FOUND);
    } catch (IllegalArgumentException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_NOT_ACCEPTABLE);
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      return $this->withJSONException($response, $exception);
      // @codeCoverageIgnoreEnd
    }
  }

  /**
   * {@inheritDoc}
   * @see \acoby\controller\RestController::list()
   */
  public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
    try {
      $user = $this->getRequestUser($request, $this->getReadUserRole());
      
      $expand = RequestUtils::getBooleanQueryParameter($request,'expand', false);
      $offset = RequestUtils::getIntegerQueryParameter($request, 'offset', 0);
      $limit = RequestUtils::getIntegerQueryParameter($request, 'limit', 100);
      
      $objects = $this->getObjects($request, $args, $expand, $offset, $limit+1, $user);
      return $this->withJSONListResponse($response, $objects, $offset, $limit);
    } catch (AccessDeniedException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_FORBIDDEN);
    } catch (ObjectNotFoundException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_NOT_FOUND);
    } catch (IllegalArgumentException $exception) {
      return $this->withJSONError($response, $exception->getMessage(),StatusCodeInterface::STATUS_NOT_ACCEPTABLE);
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      return $this->withJSONException($response, $exception);
      // @codeCoverageIgnoreEnd
    }
  }
}
