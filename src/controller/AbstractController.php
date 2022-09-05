<?php
declare(strict_types=1);

namespace acoby\controller;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use DateTime;
use Throwable;
use acoby\models\History;
use acoby\models\Job;
use acoby\services\ConfigService;
use acoby\services\HistoryService;
use acoby\services\UserService;
use acoby\system\BodyMapper;
use acoby\system\RequestBody;
use acoby\system\Utils;

abstract class AbstractController {
  const CONTENT_TYPE = "Content-Type";

  const MIMETYPE_HTML = "text/html; charset=utf-8";
  const MIMETYPE_JSON = "application/json; charset=UTF-8";

  protected $attributes = array();
  protected $mapper;

  public function __construct() {
    $this->mapper = new BodyMapper();
  }

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
   */
  protected function withJSON(ResponseInterface $response, array $data=[], int $code=StatusCodeInterface::STATUS_OK) :ResponseInterface {
    $body = new RequestBody();
    $body->write($this->mapper->mapJSON($data));
    return $response->withStatus($code)->withHeader(AbstractController::CONTENT_TYPE,AbstractController::MIMETYPE_JSON)->withBody($body);
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
        $body = new RequestBody();
        $body->write($doc);
        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR)->withBody($body);
      }
    }
  }

  /**
   *
   * @param ServerRequestInterface $request
   * @param string $name
   * @param bool $defaultValue
   * @return bool
   */
  public function getBooleanQueryParameter(ServerRequestInterface $request, string $name, bool $defaultValue) :bool {
    $queries = $request->getQueryParams();
    if (array_key_exists($name, $queries)) {
      $value = urlencode($queries[$name]);
      return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    } else {
      return $defaultValue;
    }
  }

  /**
   *
   * @param ServerRequestInterface $request
   * @param string $name
   * @param int $defaultValue
   * @return int
   */
  public function getIntegerQueryParameter(ServerRequestInterface $request, string $name, int $defaultValue) :int {
    $queries = $request->getQueryParams();
    if (array_key_exists($name, $queries)) {
      $value = urlencode($queries[$name]);
      return filter_var($value, FILTER_VALIDATE_INT);
    } else {
      return $defaultValue;
    }
  }

  /**
   *
   * @param string $key
   * @param string $defaultValue
   * @return string|NULL
   */
  public function getAttribute(string $key, string $defaultValue = null) :?string {
    if (isset($_GET[$key])) return $_GET[$key];
    if (isset($_POST[$key])) return $_POST[$key];
    if (isset($this->attributes[$key])) return $this->attributes[$key];
    return $defaultValue;
  }

  /**
   *
   * @param string $key
   * @param string $value
   */
  public function setAttribute(string $key, string $value) :void {
    $this->attributes[$key] = $value;
  }

  /**
   *
   */
  public function clear() :void {
    $this->attributes = array();
  }

  /**
   *
   * @param mixed $value
   * @return string
   */
  protected function convert($value) {
    if ($value !== null) {
      return htmlspecialchars("".$value);
    }
    return "";
  }

  /**
   *
   * @param object $object
   * @param array $items
   * @param string $title
   * @param string $name
   * @return array
   */
  protected function createHistory(object $object, array $items, string $title, string $name) :array {
    $data = array();
    $addMessage = false;
    $modifiedMessage = false;

    foreach ($items as $item) {
      if ($item instanceof History) {
        $response = $this->addHistoryEntry($data,$item,$title,$name);
      } else if ($item instanceof Job) {
        $response = $this->addJobEntry($data,$item,$title,$name);
      }

      if (isset($response["add"])) $addMessage = true;
      if (isset($response["modified"])) $modifiedMessage = true;
    }

    if (!$addMessage) {
      $day = (new DateTime($object->created))->format('Y-m-d');
      $time = (new DateTime($object->created))->format('H:i');
      $timestamp = (new DateTime($object->created))->getTimestamp();
      $data[$day][$timestamp][] = $this->createHistoryEntry($time,$title." created","fa-user","The ".strtolower($title)." ".$name." was created");
    }

    if (!$modifiedMessage) {
      $day = (new DateTime($object->changed))->format('Y-m-d');
      $time = (new DateTime($object->changed))->format('H:i');
      $timestamp = (new DateTime($object->changed))->getTimestamp();
      $data[$day][$timestamp][] = $this->createHistoryEntry($time,$title."Host last updated","fa-user","The ".strtolower($title)." ".$name." was last changed");
    }


    krsort($data);
    foreach ($data as $day => $item) {
      krsort($data[$day]);
    }
    return $data;
  }

  /**
   *
   * @param array $data
   * @param History $item
   * @param string $title
   * @param string $name
   * @return array
   */
  protected function addHistoryEntry(array &$data, History $item, string $title, string $name) :array {
    $response = array();

    $date = (new DateTime($item->created))->format('Y-m-d');
    $time = (new DateTime($item->created))->format('H:i');
    $timestamp = (new DateTime($item->created))->getTimestamp();

    $subject = $title;
    $creator = " by Unknown";
    if ($item->creatorId !== null) {
      $user = UserService::getInstance()->getUserByExternalId($item->creatorId);
      if ($user !== null) {
        $creator = " by ".$user->firstName." ".$user->lastName;
      }
    }

    if ($item->mode === HistoryService::MODE_ADD) {
      $response["add"] = true;
      $subject.= " created";
    } else if ($item->mode === HistoryService::MODE_CHANGED) {
      $response["modified"] = true;
      $subject.= " changed";
    } else if ($item->mode === HistoryService::MODE_DELETED) {
      $subject.= " deleted";
    } else {
      $response["modified"] = true;
      $subject.= " modified";
    }

    $data[$date][$timestamp][] = $this->createHistoryEntry($time,$subject,"fa-user",$item->message.$creator);

    return $response;
  }


  /**
   *
   * @param array $data
   * @param History $item
   * @param string $title
   * @param string $name
   * @return array
   */
  protected function addJobEntry(array &$data, Job $item, string $title, string $name) :array {
    $response = array();

    $date = (new DateTime($item->created))->format('Y-m-d');
    $time = (new DateTime($item->created))->format('H:i');
    $timestamp = (new DateTime($item->created))->getTimestamp();

    $subject = "Job created";
    $creator = " by Unknown";
    $message = "We created a task to update ".$title." ".$name.". This job was created";
    if ($item->creatorId !== null) {
      $user = UserService::getInstance()->getUserByExternalId($item->creatorId);
      if ($user !== null) {
        $creator = " by ".$user->firstName." ".$user->lastName;
      }
    }

    $data[$date][$timestamp][] = $this->createHistoryEntry($time,$subject,"fa-user",$message.$creator);

    if ($item->finished) {
      $date = (new DateTime($item->finished))->format('Y-m-d');
      $time = (new DateTime($item->finished))->format('H:i');
      $timestamp = (new DateTime($item->finished))->getTimestamp();

      $subject = "Job finished";
      $message = "The job for ".$title." ".$name." finished. ";
      if ($item->jobReturncode > 0) {
        $message.= "There was an unexpected error during execution.";
      } else {
        $message.= "This job was successful finished.";
      }

      $data[$date][$timestamp][] = $this->createHistoryEntry($time,$subject,"fa-user",$message.$creator);
    }

    return $response;
  }

  /**
   *
   * @param string $time
   * @param string $title
   * @param string $icon
   * @param string $content
   * @param string $footer
   * @return array
   */
  protected function createHistoryEntry(string $time, string $title, string $icon, string $content = null, string $footer = null) :array {
    $item = array();
    $item["icon"] = $icon;
    $item["time"] = $time;
    $item["title"] = $title;
    if ($content !== null) $item["content"] = $content;
    if ($footer !== null) $item["footer"] = $footer;
    return $item;
  }
}