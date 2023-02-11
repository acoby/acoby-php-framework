<?php
declare(strict_types=1);

namespace acoby\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use acoby\services\ConfigService;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use acoby\system\HttpHeader;
use acoby\system\Utils;

/**
 * A small handler to add per default server version.
 * 
 * @author Thoralf Rickert-Wendt
 */
class VersionResponseHandler implements MiddlewareInterface {
  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    $serverVersion = ConfigService::getString("version");
    $clientVersion = $request->getHeader(HttpHeader::X_CLIENT_VERSION);
    if (count($clientVersion) > 0) {
      foreach ($clientVersion as $version) {
        // must be more specfic and ignore bugfix-releases
        if ($version !== $serverVersion) {
          Utils::logError("Client Version ".$clientVersion." does not fit Server Version ".$serverVersion);
        }
      }
    } else {
      Utils::logInfo("Client did not send version information via Header ".HttpHeader::X_CLIENT_VERSION);
    }
    $response = $handler->handle($request);
    return $response->withAddedHeader(HttpHeader::X_SERVER_VERSION, $serverVersion);
  }
}