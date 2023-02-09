<?php
namespace acoby\middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ResponseFactory;
use acoby\system\Utils;
use acoby\system\RequestBody;
use Fig\Http\Message\StatusCodeInterface;
use acoby\controller\AbstractController;

class RESTErrorHandler {
  public function handleError(ServerRequestInterface $request, \Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails, $logger = null) :ResponseInterface {
    if ($logErrors) {
      Utils::logException($exception->getMessage(),$exception);
    }
    $code = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    $error = Utils::createException($code, "Could not handle request", $exception);
    $doc = json_encode($error);

    $responseFactory = new ResponseFactory();
    $body = new RequestBody();
    $body->write($doc);
    return $responseFactory->createResponse($code)->withHeader(AbstractController::CONTENT_TYPE,AbstractController::MIMETYPE_JSON)->withBody($body);
  }
}