<?php
declare(strict_types=1);

namespace acoby\system;

/**
 */
interface HttpHeader {
  // @codeCoverageIgnoreStart
  const AUTHORIZATION     = "Authorization";
  const X_FORWARDED_PROTO = "X-Forwarded-Proto";
  const X_FORWARDED_PORT  = "X-Forwarded-Port";
  const WWW_AUTHENTICATE  = "WWW-Authenticate";
  const CONTENT_TYPE      = "Content-Type";
  const X_RESULT_MORE     = "X-RESULT-more";
  const X_RESULT_OFFSET   = "X-RESULT-offset";
  const X_RESULT_LIMIT    = "X-RESULT-limit";
  const X_SERVER_VERSION  = "X-SERVER-version";
  const X_CLIENT_VERSION  = "X-CLIENT-version";
  const LOCATION          = "Location";

  const MIMETYPE_HTML     = "text/html; charset=utf-8";
  const MIMETYPE_JSON     = "application/json; charset=UTF-8";
  // @codeCoverageIgnoreEnd
}