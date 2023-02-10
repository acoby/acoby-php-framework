<?php
declare(strict_types=1);

namespace acoby\controller;

use acoby\models\AbstractUser;
use Psr\Http\Message\ServerRequestInterface;
use acoby\services\UserFactory;
use acoby\exceptions\AccessDeniedException;

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
   * @throws AccessDeniedException
   * @return AbstractUser
   */
  protected function getRequestUser(ServerRequestInterface $request, string $role = UserFactory::USER) :AbstractUser {
    $user = $request->getAttribute(AbstractController::ATTRIBUTE_KEY_USER);
    if ($user === null) {
      throw new AccessDeniedException('User is not authorized');
    }
    if (!UserFactory::getInstance()->hasRole($user, $role)) {
      throw new AccessDeniedException('User has not enough privileges');
    }
    return $user;
  }
  
  /**
   * Expects a admin user in the request
   * 
   * @param ServerRequestInterface $request
   * @return AbstractUser
   */
  protected function getRequestAdminUser(ServerRequestInterface $request) :AbstractUser {
    return $this->getRequestUser($request, UserFactory::ADMIN);
  }
  
  /**
   * expects a report user in the request
   * 
   * @param ServerRequestInterface $request
   * @return AbstractUser
   */
  protected function getRequestReportUser(ServerRequestInterface $request) :AbstractUser {
    return $this->getRequestUser($request, UserFactory::REPORT);
  }
  
}