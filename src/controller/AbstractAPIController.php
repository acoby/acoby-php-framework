<?php
declare(strict_types=1);

namespace acoby\controller;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use acoby\system\SessionManager;

abstract class AbstractAPIController extends AbstractController {
  const FORMAT_FULL = 0;
  const FORMAT_SELECT2 = 1;

  /**
   * Prüft, ob der Request korrekt abgearbeitet werden kann.
   *
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @return ResponseInterface|NULL wenn alles ok ist, kommt ein null zurück
   */
  protected function validateAPIRequest(ServerRequestInterface $request, ResponseInterface $response, array $args) :?ResponseInterface {
    $user = SessionManager::getInstance()->getUser();
    if ($user === null) {
      return $response->withStatus(StatusCodeInterface::STATUS_FORBIDDEN);
    }
    // darf der User auf diesen Punkt zugreifen?
    return null;
  }

  /**
   * Liefert die Daten aus, die für einen API Response nötig sind.
   *
   * @codeCoverageIgnore
   * @param ServerRequestInterface $request
   * @param array $args
   * @return array
   */
  public function getData(ServerRequestInterface $request, array $args, int $format = AbstractAPIController::FORMAT_FULL) :array {
    return array();
  }
}