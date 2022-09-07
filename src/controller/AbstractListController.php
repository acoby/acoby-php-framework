<?php
declare(strict_types=1);

namespace acoby\controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Throwable;
use acoby\system\Utils;

abstract class AbstractListController extends AbstractAPIController {
  /**
   * Liefert das zu rendernede Template
   *
   * @return string Ein Pfad in /templates
   */
  protected abstract function getTemplate() :string;

  /**
   * Wird genutzt, um die HTML Seite anzuzeigen.
   *
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @return ResponseInterface
   */
  public function view(ServerRequestInterface $request, ResponseInterface $response, array $args) :ResponseInterface {
    session_commit();
    $my_response = $this->validateAPIRequest($request, $response, $args);
    if ($my_response !== null) return $my_response;

    $view = Twig::fromRequest($request);

    try {
      return $this->withTwig($response, $view, $this->getTemplate(), $this->getTwigArgs($request, $args));
      // @codeCoverageIgnoreStart
    } catch (Throwable $throwable) {
      Utils::logException("Problem during rendering",$throwable);
      return $this->withError($response, $view, "Unknown Error",$throwable);
      // @codeCoverageIgnoreEnd
    }
  }

  /**
   * Liefert eine Liste von Args, die für das Template-Rendering nötig ist.
   *
   * @codeCoverageIgnore
   * @param ServerRequestInterface $request
   * @param array $args
   * @return array
   */
  protected function getTwigArgs(ServerRequestInterface $request, array $args) :array {
    return [];
  }

  /**
   * Wird genutzt, um die JSON Daten auszugeben.
   *
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @return ResponseInterface
   */
  public function list(ServerRequestInterface $request, ResponseInterface $response, array $args) :ResponseInterface {
    session_commit();
    $my_response = $this->validateAPIRequest($request, $response, $args);
    if ($my_response !== null) return $my_response;

    $list = $this->getData($request, $args);
    return $this->withJSON($response,$list);
  }

  /**
   * Wird genutzt, um die JSON Daten auszugeben.
   *
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @return ResponseInterface
   */
  public function values(ServerRequestInterface $request, ResponseInterface $response, array $args) :ResponseInterface {
    session_commit();
    $my_response = $this->validateAPIRequest($request, $response, $args);
    if ($my_response !== null) return $my_response;

    $list = $this->getData($request, $args, AbstractAPIController::FORMAT_SELECT2);
    return $this->withJSON($response,$list);
  }
}