<?php
declare(strict_types=1);

namespace acoby\controller;

use acoby\exceptions\IllegalStateException;
use acoby\models\AbstractUser;
use Psr\Http\Message\ServerRequestInterface;
use acoby\exceptions\AccessDeniedException;
use acoby\services\AbstractFactory;
use acoby\services\UserService;

/**
 * a base class for responding REST backend controllers. Implementing controllers need to be stateless
 * 
 * @author Thoralf Rickert-Wendt
 */
abstract class AbstractRESTController extends AbstractController {
  /**
   * Returns the current request user based on the request attribute. This controller is stateless.
   * 
   * @param ServerRequestInterface $request
   * @param string $role
   * @return AbstractUser
   *@throws AccessDeniedException|IllegalStateException
   */
  protected function getRequestUser(ServerRequestInterface $request, string $role = UserService::USER) :AbstractUser {
    $user = $request->getAttribute(AbstractController::ATTRIBUTE_KEY_USER);
    if ($user === null) {
      throw new AccessDeniedException('User is not authorized');
    }
    if (!AbstractFactory::getUserService()->hasRole($user, $role)) {
      throw new AccessDeniedException('User has not enough privileges');
    }
    return $user;
  }

  /**
   * Expects an admin user in the request
   *
   * @param ServerRequestInterface $request
   * @return AbstractUser
   * @throws AccessDeniedException
   * @throws IllegalStateException
   */
  protected function getRequestAdminUser(ServerRequestInterface $request) :AbstractUser {
    return $this->getRequestUser($request, UserService::ADMIN);
  }

  /**
   * expects a report user in the request
   *
   * @param ServerRequestInterface $request
   * @return AbstractUser
   * @throws AccessDeniedException
   * @throws IllegalStateException
   */
  protected function getRequestReportUser(ServerRequestInterface $request) :AbstractUser {
    return $this->getRequestUser($request, UserService::REPORT);
  }
  
}