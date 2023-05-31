<?php
namespace acoby\exceptions;

use Exception;
use Throwable;
use acoby\models\RESTStatus;

class BackendException extends Exception {
  public $status;

  /**
   * @param string $message
   * @param RESTStatus $response
   * @param int|null $code
   * @param Throwable|null $previous
   */
  public function __construct (string $message, RESTStatus $response, int $code = null, Throwable $previous = null) {
    parent::__construct($message,$code,$previous);
    $this->status = $response;
  }
}
