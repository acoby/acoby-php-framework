<?php
namespace acoby\exceptions;

use Exception;
use Throwable;
use acoby\models\RESTStatus;

class BackendException extends Exception {
  public $status;
  
  public function __construct (string $message = null, RESTStatus $response, int $code = null, Throwable $previous = null) {
    parent::__construct($message,$code,$previous);
    $this->status = $response;
  }
}
