<?php
namespace acoby\exceptions;

use Exception;

class AccessDeniedException extends Exception {
  public function __construct ($message = null, $code = null, $previous = null) {
    parent::__construct($message,$code,$previous);
  }
}
