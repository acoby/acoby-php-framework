<?php
declare(strict_types=1);

namespace acoby\controller;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use acoby\system\BodyMapper;
use acoby\system\RequestBody;
use acoby\system\Utils;
use acoby\system\HttpHeader;
use acoby\system\RequestUtils;

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
    $body = new RequestBody();
    $body->write(json_encode($data));
    return $response->withStatus($code)->withHeader(AbstractController::CONTENT_TYPE,AbstractController::MIMETYPE_JSON)->withBody($body);
  }
  
  /**
   * Converts an array into JSON and returns that as a HTTP Response
   *
   * @param ResponseInterface $response
   * @param array $data
   * @param int $code
   * @return ResponseInterface
   */
  protected function withJSONObjectList(ResponseInterface $response, array $data, int $code=StatusCodeInterface::STATUS_OK) :ResponseInterface {
    $body = new RequestBody();
    $body->write(json_encode($data));
    return $response->withStatus($code)->withHeader(AbstractController::CONTENT_TYPE,AbstractController::MIMETYPE_JSON)->withBody($body);
  }
  
  /**
   *
   * @param ResponseInterface $response
   * @param array $data
   * @param int $code
   * @return ResponseInterface
   */
  protected function withJSONError(ResponseInterface $response, string $message, int $code=StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR) :ResponseInterface {
    $status = Utils::createError($code,$message);
    return $this->withJSONObject($response, $status, $code);
  }
  
  /**
   *
   * @deprecated Please use RequestUtils::getBooleanQueryParameter
   * @param ServerRequestInterface $request
   * @param string $name
   * @param bool $defaultValue
   * @return bool
   */
  public function getBooleanQueryParameter(ServerRequestInterface $request, string $name, bool $defaultValue) :bool {
    return RequestUtils::getBooleanQueryParameter($request, $name, $defaultValue);
  }

  /**
   *
   * @deprecated Please use RequestUtils::getIntegerQueryParameter
   * @param ServerRequestInterface $request
   * @param string $name
   * @param int $defaultValue
   * @return int
   */
  public function getIntegerQueryParameter(ServerRequestInterface $request, string $name, int $defaultValue) :int {
    return RequestUtils::getIntegerQueryParameter($request, $name, $defaultValue);
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