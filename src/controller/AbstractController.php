<?php
declare(strict_types=1);

namespace acoby\controller;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use acoby\system\BodyMapper;
use acoby\system\RequestBody;
use acoby\system\Utils;
use acoby\system\RequestUtils;

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
   * @param ServerRequestInterface $request
   * @param string $name
   * @param int $defaultValue
   * @return int
   */
  public function getIntegerQueryParameter(ServerRequestInterface $request, string $name, int $defaultValue) :int {
    return RequestUtils::getIntegerQueryParameter($request, $name, $defaultValue);
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
}