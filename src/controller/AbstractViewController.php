<?php
declare(strict_types=1);

namespace acoby\controller;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;
use Throwable;
use acoby\services\ConfigService;
use acoby\models\AbstractUser;
use acoby\system\Utils;

abstract class AbstractViewController extends AbstractController {
  /**
   * Return the currently logged in user
   *
   * @return AbstractUser|NULL
   */
  protected abstract function getCurrentUser() :?AbstractUser;
  
  /**
   *
   * @param ResponseInterface $response
   * @param Twig $view
   * @param string $template
   * @param array $data
   * @param int $code
   * @return ResponseInterface
   */
  protected function withTwig(ResponseInterface $response, Twig $view, string $template, array $data=[], int $code=StatusCodeInterface::STATUS_OK) :ResponseInterface {
    return $view->render($response->withStatus($code)->withHeader(AbstractController::CONTENT_TYPE,AbstractController::MIMETYPE_HTML), $template, $data);
  }

  /**
   *
   * @param ResponseInterface $response
   * @param array $data
   * @param int $code
   * @return ResponseInterface
   * @deprecated see withJSONObjectList
   */
  protected function withJSON(ResponseInterface $response, array $data=[], int $code=StatusCodeInterface::STATUS_OK) :ResponseInterface {
    return $this->withJSONArray($response, $data, $code);
  }

  /**
   * @codeCoverageIgnore
   * @param ResponseInterface $response
   * @param Twig $view
   * @param string $message
   * @param Throwable $throwable
   * @param int $code
   * @return \Psr\Http\Message\ResponseInterface
   */
  protected function withError(ResponseInterface $response, Twig $view, string $message = "Unknown error", Throwable $throwable = null, int $code = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR) {
    try {
      $data = array();
      if ($throwable !== null) {
        $data["error"] = $throwable;
      }
      $data["message"] = $message;
      return $this->withTwig($response, $view, 'error.html',$data, $code);
    } catch (Throwable $t1) {
      Utils::logException("Problem during rendering error message",$t1);
      try {
        $data = array();
        if ($throwable !== null) {
          $data["error"] = $throwable;
        }
        $data["message"] = $message;
        return $this->withTwig($response, $view, 'login/error.html',$data, StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
      } catch (Throwable $t2) {
        Utils::logException("Problem during rendering second error message",$t2);

        $doc = "<html><head><title>Error</title></head><body><h3>Error</h3><p>";
        $doc.= $message."<br/>";
        if (ConfigService::getString("acoby_environment") !== "prod") {
          if ($throwable !== null) {
            $doc.= $throwable->getMessage()."<br/>";
            if ($throwable->getPrevious() !== null) $doc.= $throwable->getMessage()."<br/>";
            $doc.= " in ".$throwable->getFile().":".$throwable->getLine()."<br/>";
            $doc.= nl2br($throwable->getTraceAsString());
          }
          $doc.= "</p><p>Rendering error message also produces second error</p><p>";
          $doc.= $t1->getMessage()."<br/>";
          if ($t1->getPrevious() !== null) $doc.= $t1->getMessage()."<br/>";
          $doc.= " in ".$t1->getFile().":".$t1->getLine()."<br/>";
          $doc.= nl2br($t1->getTraceAsString());
          $doc.= "</p><p>Rendering second error message also produces third error</p><p>";
          $doc.= $t2->getMessage()."<br/>";
          if ($t2->getPrevious() !== null) $doc.= $t2->getMessage()."<br/>";
          $doc.= " in ".$t2->getFile().":".$t2->getLine()."<br/>";
          $doc.= nl2br($t2->getTraceAsString());
        } else {
          $doc.= "There was an Error. Details are written to logfile";
        }
        $doc.="</p></body></html>";
        $body = $response->getBody();
        $body->write($doc);
        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR)->withBody($body);
      }
    }
  }

  /**
   *
   * @param mixed $value
   * @return string
   */
  protected function convert($value, $defaultValue = "") {
    if ($value !== null) {
      return htmlspecialchars(strval($value));
    }
    return $defaultValue;
  }
}