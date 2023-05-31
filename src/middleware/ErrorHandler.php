<?php
namespace acoby\middleware;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ResponseFactory;
use acoby\system\Utils;
use acoby\system\RequestBody;
use Throwable;

class ErrorHandler {
  /**
   * @param ServerRequestInterface $request
   * @param Throwable $exception
   * @param bool $displayErrorDetails
   * @param bool $logErrors
   * @param bool $logErrorDetails
   * @param $logger
   * @return ResponseInterface
   */
  public function handleError(ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails, $logger = null) :ResponseInterface {
    if ($logErrors) {
      Utils::logException($exception->getMessage(),$exception);
    }

    $doc = "<html><head><title>Error</title></head><body><h3>Error</h3><p>";
    if ($displayErrorDetails) {
      $doc.= $exception->getMessage()."<br/>";
      $doc.= " in ".$exception->getFile().":".$exception->getLine()."<br/>";
      $doc.= nl2br($exception->getTraceAsString());
      if ($exception->getPrevious() !== null) {
        $previous = $exception->getMessage();
        $doc.= $previous."<br/>";

      }
    } else {
      $doc.= "There was an Error. Details are written to logfile";
    }
    $doc.="</p></body></html>";

    $responseFactory = new ResponseFactory();
    $body = new RequestBody();
    $body->write($doc);
    return $responseFactory->createResponse(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR)->withHeader("Content-Type","text/html; charset=utf-8")->withBody($body);
  }
}