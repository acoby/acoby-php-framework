<?php
namespace acoby\exceptions;

use RuntimeException;

class DatabaseException extends RuntimeException {
  public function __construct($message = null, $code = null, $previous = null) {
    parent::__construct($message,$code,$previous);
  }
}
