<?php
declare(strict_types=1);

namespace acoby\system;

use \Psr\Http\Message\ResponseInterface;
use \GuzzleHttp\Client;
use \acoby\services\ConfigService;

class HTTPClient {
  /** @var Client */
  private $instance;

  /**
   *
   * @param array $config
   */
  public function __construct(array $config = []) {
    $this->instance = new Client($config);
  }

  /**
   *
   * @param string $method
   * @param string $uri
   * @param array $options
   * @return ResponseInterface
   */
  public function request(string $method, string $uri = '', array $options = []) :?ResponseInterface {
    $wrapper = $this->wrap($method, $uri, $options);
    if ($wrapper !== null) {
      return $wrapper;
    } else {
      // @codeCoverageIgnoreStart
      if (ConfigService::getString("acoby_environment") !== "prod") {
        error_log("[CALL] ".$method." ".$uri);
      }
      return $this->instance->request($method, $uri, $options);
      // @codeCoverageIgnoreEnd
    }
  }

  /**
   *
   * @param string $method
   * @param string $uri
   * @param array $options
    * @return ResponseInterface
   */
  private function wrap(string $method, string $uri = '', array $options) :?ResponseInterface {
    global $ACOBY_CONFIG;

    $key = $method." ".$uri;

    foreach ($ACOBY_CONFIG["test"]["http_wrapper"] as $match => $value) {
      if (preg_match($match, $key)>0) {
        $result = call_user_func_array($value, array($method,$uri,$options));
        return $result;
      }
    }

    // @codeCoverageIgnoreStart
    return null;
    // @codeCoverageIgnoreEnd
  }
}