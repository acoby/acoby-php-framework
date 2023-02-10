<?php
declare(strict_types=1);

namespace acoby\controller;

use acoby\models\AbstractUser;
use Psr\Http\Message\ServerRequestInterface;
use acoby\services\UserFactory;
use acoby\exceptions\AccessDeniedException;

abstract class AbstractRESTController extends AbstractController {
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
  
  protected function getRequestAdminUser(ServerRequestInterface $request) :AbstractUser {
    return $this->getRequestUser($request, UserFactory::ADMIN);
  }
  
  protected function getRequestReportUser(ServerRequestInterface $request) :AbstractUser {
    return $this->getRequestUser($request, UserFactory::REPORT);
  }
  
}