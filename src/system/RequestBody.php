<?php
declare(strict_types=1);

namespace acoby\system;

use Slim\Psr7\Stream;

class RequestBody extends Stream {
  public function __construct() {
    $stream = fopen('php://temp', 'w+');
    stream_copy_to_stream(fopen('php://input', 'r'), $stream);
    rewind($stream);

    parent::__construct($stream);
  }
}
