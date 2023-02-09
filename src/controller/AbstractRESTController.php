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
use acoby\system\Utils;
use acoby\exceptions\ObjectNotFoundException;
use acoby\services\UserFactory;
use acoby\models\AbstractUser;

abstract class AbstractRESTController extends AbstractController implements RestController {
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
  protected abstract function searchObjects(AbstractSearch $search, AbstractUser $user) :array;
  
  /**
   * @param object $object
   * @param AbstractUser $user
   * @return object|NULL
   * @throws IllegalArgumentException
   */
  protected abstract function createObject(object $object, AbstractUser $user) :?object;

  /**
   * @param object $object
   * @param AbstractUser $user
   * @return object|NULL
   * @throws IllegalArgumentException
   */
  protected abstract function updateObject(object $object, AbstractUser $user) :?object;
  
  /**
   * @param object $object
   * @param AbstractUser $user
   * @return bool
   */
  protected abstract function deleteObject(object $object, AbstractUser $user) :bool;
  
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
  protected abstract function getObjects(bool $expand, int $offset, int $limit, AbstractUser $user) :array;

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
  protected function getCreateUserRole() :string {
    return UserFactory::USER;
  }
  
  /**
   * @return string
   */
  protected function getReadUserRole() :string {
    return UserFactory::USER;
  }

  /**
   * @return string
   * @codeCoverageIgnore
   */
  protected function getUpdateUserRole() :string {
    return UserFactory::USER;
  }
  
  /**
   * @return string
   * @codeCoverageIgnore
   */
  protected function getDeleteUserRole() :string {
    return UserFactory::USER;
  }
  
  /**
   * {@inheritDoc}
   * @see \acoby\controller\RestController::create()
   */
  public function create(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
    try {
      $user = $this->getRequestUser($request, $this->getCreateUserRole());
      $object = $this->mapper->map($request->getBody()->__toString(), $this->getNewObject());
      
      $newObject = $this->createObject($object, $user);
      if ($newObject === null) throw new IllegalArgumentException('Invalid input');
      return $response->withStatus(StatusCodeInterface::HTTP_CREATED)->withJson($newObject);
    } catch (AccessDeniedException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_FORBIDDEN)->withJson(Utils::createError(StatusCodeInterface::HTTP_FORBIDDEN,$exception->getMessage()));
    } catch (IllegalArgumentException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_NOT_ACCEPTABLE)->withJson(Utils::createError(StatusCodeInterface::HTTP_NOT_ACCEPTABLE,$exception->getMessage()));
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      Utils::logException($exception->getMessage(), $exception);
      return $response->withStatus(StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR)->withJson(Utils::createException(StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage(),$exception));
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
      $object = $this->getObject($request,$args,$user);
      if ($object === null) throw new ObjectNotFoundException('Invalid input');
      return $response->withStatus(StatusCodeInterface::HTTP_OK)->withJson($object);
    } catch (AccessDeniedException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_FORBIDDEN)->withJson(Utils::createError(StatusCodeInterface::HTTP_FORBIDDEN,$exception->getMessage()));
    } catch (IllegalArgumentException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_NOT_ACCEPTABLE)->withJson(Utils::createError(StatusCodeInterface::HTTP_NOT_ACCEPTABLE,$exception->getMessage()));
    } catch (ObjectNotFoundException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_NOT_FOUND)->withJson(Utils::createError(StatusCodeInterface::HTTP_NOT_FOUND,$exception->getMessage()));
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      Utils::logException($exception->getMessage(), $exception);
      return $response->withStatus(StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR)->withJson(Utils::createException(StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage(),$exception));
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
      $oldObject = $this->getObject($request,$args,$user);
      if ($oldObject === null) throw new ObjectNotFoundException('Invalid input');

      $object = $this->mapper->map($request->getBody()->__toString(), $this->getNewObject());
      $this->compareObject($oldObject,$object,$user);
      
      $newObject = $this->updateObject($object,$user);
      if ($newObject === null) throw new IllegalArgumentException('Invalid input');
      
      return $response->withStatus(StatusCodeInterface::HTTP_ACCEPTED)->withJson($newObject);
    } catch (AccessDeniedException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_FORBIDDEN)->withJson(Utils::createError(StatusCodeInterface::HTTP_FORBIDDEN,$exception->getMessage()));
    } catch (IllegalArgumentException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_NOT_ACCEPTABLE)->withJson(Utils::createError(StatusCodeInterface::HTTP_NOT_ACCEPTABLE,$exception->getMessage()));
    } catch (ObjectNotFoundException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_NOT_FOUND)->withJson(Utils::createError(StatusCodeInterface::HTTP_NOT_FOUND,$exception->getMessage()));
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      Utils::logException($exception->getMessage(), $exception);
      return $response->withStatus(StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR)->withJson(Utils::createException(StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage(),$exception));
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
      $object = $this->getObject($request,$args,$user);
      if ($object === null) throw new ObjectNotFoundException('Invalid input');
      
      $state = $this->deleteObject($object,$user);
      if (!$state) {
        // @codeCoverageIgnoreStart
        throw new IllegalArgumentException('Object could not be deleted');
        // @codeCoverageIgnoreEnd
      }
      return $response->withStatus(StatusCodeInterface::HTTP_ACCEPTED)->withJson(Utils::createResult(StatusCodeInterface::HTTP_ACCEPTED, 'Object deleted'));
    } catch (AccessDeniedException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_FORBIDDEN)->withJson(Utils::createError(StatusCodeInterface::HTTP_FORBIDDEN,$exception->getMessage()));
    } catch (IllegalArgumentException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_NOT_ACCEPTABLE)->withJson(Utils::createError(StatusCodeInterface::HTTP_NOT_ACCEPTABLE,$exception->getMessage()));
    } catch (ObjectNotFoundException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_NOT_FOUND)->withJson(Utils::createError(StatusCodeInterface::HTTP_NOT_FOUND,$exception->getMessage()));
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      Utils::logException($exception->getMessage(), $exception);
      return $response->withStatus(StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR)->withJson(Utils::createException(StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage(),$exception));
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

      $objects = $this->searchObjects($search,$user);

      return $this->getListResponse($response, $objects, $search->offset, $search->limit);
    } catch (AccessDeniedException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_FORBIDDEN)->withJson(Utils::createError(StatusCodeInterface::HTTP_FORBIDDEN,$exception->getMessage()));
    } catch (IllegalArgumentException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_NOT_ACCEPTABLE)->withJson(Utils::createError(StatusCodeInterface::HTTP_NOT_ACCEPTABLE,$exception->getMessage()));
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      Utils::logException($exception->getMessage(), $exception);
      return $response->withStatus(StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR)->withJson(Utils::createException(StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage(),$exception));
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
      
      $expand = Utils::getBooleanQueryParameter($request,'expand', false);
      $offset = Utils::getIntegerQueryParameter($request, 'offset', 0);
      $limit = Utils::getIntegerQueryParameter($request, 'limit', 100);
      
      $list = $this->getObjects($expand,$offset,$limit+1,$user);
      return $this->getListResponse($response, $list, $offset, $limit);
    } catch (AccessDeniedException $exception) {
      return $response->withStatus(StatusCodeInterface::HTTP_FORBIDDEN)->withJson(Utils::createError(StatusCodeInterface::HTTP_FORBIDDEN,$exception->getMessage()));
      // @codeCoverageIgnoreStart
    } catch (Exception $exception) {
      Utils::logException($exception->getMessage(), $exception);
      return $response->withStatus(StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR)->withJson(Utils::createException(StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR,$exception->getMessage(),$exception));
      // @codeCoverageIgnoreEnd
    }
  }
}
