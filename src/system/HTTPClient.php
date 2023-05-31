<?php
declare(strict_types=1);

namespace acoby\system;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;
use acoby\services\ConfigService;

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
   * @param string $uri
   * @param array $options
   * @return ResponseInterface|NULL
   * @throws GuzzleException
   */
  public function get(string $uri = '', array $options = []) :?ResponseInterface {
    return $this->request("GET",$uri,$options);
  }

  /**
   *
   * @param string $uri
   * @param array $options
   * @return ResponseInterface|NULL
   * @throws GuzzleException
   */
  public function post(string $uri = '', array $options = []) :?ResponseInterface {
    return $this->request("POST",$uri,$options);
  }

  /**
   *
   * @param string $uri
   * @param array $options
   * @return ResponseInterface|NULL
   * @throws GuzzleException
   */
  public function put(string $uri = '', array $options = []) :?ResponseInterface {
    return $this->request("PUT",$uri,$options);
  }

  /**
   *
   * @param string $uri
   * @param array $options
   * @return ResponseInterface|NULL
   * @throws GuzzleException
   */
  public function delete(string $uri = '', array $options = []) :?ResponseInterface {
    return $this->request("DELETE",$uri,$options);
  }

  /**
   *
   * @param string $method
   * @param string $uri
   * @param array $options
   * @return ResponseInterface
   * @throws GuzzleException
   */
  public function request(string $method, string $uri = '', array $options = []) :?ResponseInterface {
    // this is a specific wrapper for testing frontends which are heavily calling backends
    // to avoid accidently calling the backend. In test environments we call a special callable
    // like a mock to respond correctly as it would be a live backend
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
   * this is a specific wrapper for testing frontends which are heavily calling backends
   * to avoid accidently calling the backend. In test environments we call a special callable
   * like a mock to respond correctly as it would be a live backend
   *
   * @param string $method
   * @param string $uri
   * @param array $options
   * @return ResponseInterface
   */
  private function wrap(string $method, string $uri = '', array $options = []) :?ResponseInterface {
    $key = $method." ".$uri;
    
    foreach (ConfigService::getArray("test.http_wrapper",array()) as $match => $value) {
      if (preg_match($match, $key)>0) {
        return call_user_func_array($value, array($method,$uri,$options));
      }
    }
    
    // @codeCoverageIgnoreStart
    return null;
    // @codeCoverageIgnoreEnd
  }
}