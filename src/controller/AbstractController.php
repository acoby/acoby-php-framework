<?php
declare(strict_types=1);

namespace acoby\controller;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use acoby\system\BodyMapper;
use acoby\system\Utils;
use acoby\system\HttpHeader;
use Throwable;
use acoby\system\RequestUtils;

/**
 * This is the base controller for all Slim requests. It contains only some helper methods and constants.
 * 
 * @author Thoralf Rickert-Wendt
 */
abstract class AbstractController {
  const CONTENT_TYPE = HttpHeader::CONTENT_TYPE;

  const MIMETYPE_HTML = HttpHeader::MIMETYPE_HTML;
  const MIMETYPE_JSON = HttpHeader::MIMETYPE_JSON;
  
  const ATTRIBUTE_KEY_USER = "user";
  
  /** @var $attributes array[] */
  protected $attributes = array();
  /** @var $mapper BodyMapper */
  protected $mapper;

  public function __construct() {
    $this->mapper = new BodyMapper();
  }
  
  /**
   * Converts an object into JSON and returns that as a HTTP Response
   *
   * @param ResponseInterface $response
   * @param $data
   * @param int $code
   * @return ResponseInterface
   */
  protected function withJSONObject(ResponseInterface $response, object $data, int $code=StatusCodeInterface::STATUS_OK) :ResponseInterface {
    $body = $response->getBody();
    $body->write(json_encode($data));
    return $response->withStatus($code)->withHeader(HttpHeader::CONTENT_TYPE,HttpHeader::MIMETYPE_JSON)->withBody($body);
  }
  
  /**
   * Converts an array into JSON and returns that as a HTTP Response
   *
   * @param ResponseInterface $response
   * @param array $data
   * @param int $code
   * @return ResponseInterface
   * @deprecated use withJSONArray()
   */
  protected function withJSONObjectList(ResponseInterface $response, array $data, int $code=StatusCodeInterface::STATUS_OK) :ResponseInterface {
    return $this->withJSONArray($response, $data, $code);
  }
  
  /**
   * Converts an array into JSON and returns that as a HTTP Response
   * 
   * @param ResponseInterface $response
   * @param array $data
   * @param int $code
   * @return ResponseInterface
   */
  protected function withJSONArray(ResponseInterface $response, array $data, int $code=StatusCodeInterface::STATUS_OK) :ResponseInterface {
    $body = $response->getBody();
    $body->write(json_encode($data));
    return $response->withStatus($code)->withHeader(HttpHeader::CONTENT_TYPE,HttpHeader::MIMETYPE_JSON)->withBody($body);
  }
  
  /**
   * Converts an array into JSON and returns that as a HTTP Response
   * 
   * @param ResponseInterface $response
   * @param array $list
   * @param int $offset
   * @param int $limit
   * @param int $httpStatus
   * @return ResponseInterface
   */
  protected function withJSONListResponse(ResponseInterface $response, array $list, int $offset = 0, int $limit = 100, int $httpStatus = StatusCodeInterface::STATUS_OK) :ResponseInterface {
    // we should search always $limit+1 to find out, that there are more objects,
    // then we pop that value from list and have a hint about more results
    if (count($list)>$limit) {
      array_pop($list);
      $response = $response->withAddedHeader(HttpHeader::X_RESULT_MORE, Utils::bool2str(true));
    } else {
      $response = $response->withAddedHeader(HttpHeader::X_RESULT_MORE, Utils::bool2str(false));
    }
    $response = $response->withAddedHeader(HttpHeader::X_RESULT_SIZE, count($list));
    $response = $response->withAddedHeader(HttpHeader::X_RESULT_OFFSET, $offset);
    $response = $response->withAddedHeader(HttpHeader::X_RESULT_LIMIT, $limit);
    return $this->withJSONArray($response, $list);
  }
  
  /**
   *
   * @param ResponseInterface $response
   * @param array $data
   * @param int $code
   * @return ResponseInterface
   */
  protected function withJSONError(ResponseInterface $response, string $message, int $code=StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR) :ResponseInterface {
    $status = RequestUtils::createError($code,$message);
    return $this->withJSONObject($response, $status, $code);
  }
  
  /**
   * 
   * @param ResponseInterface $response
   * @param string $message
   * @param Throwable $throwable
   * @param int $code
   * @return ResponseInterface
   */
  protected function withJSONException(ResponseInterface $response, Throwable $throwable, int $code=StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR, string $message = null) :ResponseInterface {
    if ($message === null) $message = $throwable->getMessage();
    $status = RequestUtils::createException($code, $message, $throwable);
    return $this->withJSONObject($response, $status, $code);
  }
  
  /**
   *
   * @param ResponseInterface $response
   * @param array $list
   * @param int $httpStatus
   * @return ResponseInterface
   * @deprecated please use withJSONObjectList
   */
  protected function getJSONResponse(ResponseInterface $response, array $list, int $httpStatus = StatusCodeInterface::STATUS_OK) :ResponseInterface {
    return $this->withJSONArray($response, $list);
  }
  
  /**
   * Gets a specific attribute from this request (request scope only)
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
   * Sets a specific attribute from this request (request scope only)
   *
   * @param string $key
   * @param string $value
   */
  public function setAttribute(string $key, string $value) :void {
    $this->attributes[$key] = $value;
  }

  /**
   * Removes all request specific and custom entries from the attribute list.
   */
  public function clear() :void {
    $this->attributes = array();
  }
}