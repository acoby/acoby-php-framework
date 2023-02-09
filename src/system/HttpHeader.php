<?php
declare(strict_types=1);

namespace acoby\system;

interface HttpHeader {
  const AUTHORIZATION = "Authorization";
  const X_FORWARDED_PROTO = "X-Forwarded-Proto";
  const X_FORWARDED_PORT = "X-Forwarded-Port";
  const WWW_AUTHENTICATE = "WWW-Authenticate";
}