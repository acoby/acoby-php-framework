<?php
declare(strict_types=1);

namespace acoby\controller;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Throwable;
use acoby\system\Utils;

/**
 *
 * @author thoralf
 */
abstract class AbstractEditController extends AbstractController {
  const VIEW_MODE_EDIT = 1;
  const VIEW_MODE_ADD = 2;
  /**
   * Liefert das zu rendernede Template
   *
   * @return string Ein Pfad in /templates
   */
  protected abstract function getTemplate(int $mode = AbstractEditController::VIEW_MODE_EDIT) :string;

  /**
   * Liefert das Objekt, das editiert werden soll.
   *
   * @param ServerRequestInterface $request
   * @param array $args
   * @return object|NULL
   */
  protected abstract function getObject(ServerRequestInterface $request, array $args) :?object;

  /**
   * @return object|NULL
   */
  protected abstract function newObject() :object;

  /**
   * Erzeugt ein Form Objekt.
   *
   * @param ServerRequestInterface $request
   * @param array $args
   * @param object $object
   * @return array
   */
  protected abstract function getForm(ServerRequestInterface $request, array $args, object $object = null) :array;

  /**
   * Löscht das Objekt.
   *
   * @param object $object
   * @return bool
   */
  protected abstract function deleteObject(object $object) :bool;

  /**
   * Speichert das Objekt.
   *
   * @param object $object
   * @return bool
   */
  protected abstract function saveObject(object $object) :bool;

  /**
   * Erzeugt ein neues Object.
   *
   * @param object $object
   * @return object|NULL
   */
  protected abstract function addObject(object $object) :?object;

  /**
   * Validiert die Form, wenn kein "cancel" oder "delete" kam.
   *
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @param array $form
   * @param object $object
   * @return bool true, wenn die Validierung erfolgreich war.
   */
  protected function validate(ServerRequestInterface $request, ResponseInterface $response, array $args, array &$form, object $object) :bool {
    $isValid = true;
    foreach ($form["elements"] as &$element) {
      $defaultValue = null;
      if (isset($element["value"])) {
        $defaultValue = strval($element["value"]);
      }
      $value = strval($this->getAttribute($element["name"],$defaultValue));
      if ($element["mandatory"] && $defaultValue !== $value && Utils::isEmpty($value)) {
        $element["error"] = $element["label"]." must be defined";
        $isValid = false;
      }
      if (isset($element["validator"])) {
        $valid = call_user_func_array($element["validator"],[$object,$element,$value]);
        if (!$valid) $isValid = false;
      }
      $element["value"] = $value;
    }

    return $isValid;
  }

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
    $view = Twig::fromRequest($request);

    $object = $this->getObject($request, $args);
    if ($object === null) return $this->withError($response, $view, "Object not found", null, StatusCodeInterface::STATUS_NOT_FOUND);

    $form = $this->getForm($request, $args, $object);
    $my_response = $this->validateFormRequest($request, $response, $args, $form, $object);
    if ($my_response !== null) return $my_response;

    try {
      $data = $this->getTwigArgs($request, $args);
      $data["form"] = $form;
      return $this->withTwig($response, $view, $this->getTemplate(AbstractEditController::VIEW_MODE_EDIT), $data);
    } catch (Throwable $throwable) {
      Utils::logException("Problem during rendering",$throwable);
      return $this->withError($response, $view, "Unknown Error",$throwable);
    }
  }

  /**
   * Liefert eine Liste von Args, die für das Template-Rendering nötig ist.
   *
   * @param ServerRequestInterface $request
   * @param array $args
   * @return array
   */
  protected function getTwigArgs(ServerRequestInterface $request, array $args) :array {
    return [];
  }

  /**
   *
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @return ResponseInterface
   */
  public function add(ServerRequestInterface $request, ResponseInterface $response, array $args) :ResponseInterface {
    session_commit();
    $view = Twig::fromRequest($request);

    $form = $this->getForm($request, $args);

    $user = $this->getCurrentUser();
    if ($user === null) {
      return $response->withStatus(StatusCodeInterface::STATUS_FORBIDDEN);
    }

    $action = $this->getAttribute("action");
    try {
      switch ($action) {
        case "save": {
          $object = $this->newObject();
          if ($this->validate($request, $response, $args, $form, $object)) {
            $object = $this->addObject($object);
            if ($object !== null) {
              return $response->withHeader("Location", $this->getOverviewForward())->withStatus(StatusCodeInterface::STATUS_FOUND);
            }
          }
          $data = $this->getTwigArgs($request, $args);
          $data["form"] = $form;
          return $this->withTwig($response, $view, $this->getTemplate(AbstractEditController::VIEW_MODE_EDIT), $data);
        }
        case "cancel": {
          return $response->withHeader("Location", $this->getOverviewForward())->withStatus(StatusCodeInterface::STATUS_FOUND);
        }
        default: {
          $data = $this->getTwigArgs($request, $args);
          $data["form"] = $form;
          return $this->withTwig($response, $view, $this->getTemplate(AbstractEditController::VIEW_MODE_ADD), $data);
          break;
        }
      }
      // @codeCoverageIgnoreStart
    } catch (Throwable $throwable) {
      Utils::logException("Problem during rendering",$throwable);
      return $this->withError($response, $view, "Unknown Error",$throwable);
      // @codeCoverageIgnoreEnd
    }
  }


  /**
   * Wird aufgerufen, wenn ein Objekt bearbeitet wurde. Es gibt eine spezielle Action-Form, die aussagt, was zu tun ist.
   *
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @return ResponseInterface
   */
  public function edit(ServerRequestInterface $request, ResponseInterface $response, array $args) :ResponseInterface {
    $object = $this->getObject($request, $args);
    $form = $this->getForm($request, $args, $object);
    $my_response = $this->validateFormRequest($request, $response, $args, $form, $object);
    if ($my_response !== null) return $my_response;

    $action = $this->getAttribute("action");
    switch ($action) {
      case "cancel": {
        return $response->withHeader("Location", $this->getOverviewForward())->withStatus(StatusCodeInterface::STATUS_FOUND);
      }
      case "delete": {
        $this->deleteObject($object);
        return $response->withHeader("Location", $this->getOverviewForward())->withStatus(StatusCodeInterface::STATUS_FOUND);
      }
      default: {
        $view = Twig::fromRequest($request);
        if ($this->validate($request, $response, $args, $form, $object)) {
          if ($this->saveObject($object)) {
            return $response->withHeader("Location", $this->getOverviewForward())->withStatus(StatusCodeInterface::STATUS_FOUND);
          }
        }

        try {
          $data = $this->getTwigArgs($request, $args);
          $data["form"] = $form;
          return $this->withTwig($response, $view, $this->getTemplate(AbstractEditController::VIEW_MODE_EDIT), $data);
          // @codeCoverageIgnoreStart
        } catch (Throwable $throwable) {
          Utils::logException("Problem during rendering",$throwable);
          return $this->withError($response, $view, "Unknown Error",$throwable);
          // @codeCoverageIgnoreEnd
        }
      }
    }
  }

  /**
   * Liefert den Forward-Pfad, zu dem wir gehen, wenn die Bearbeitung abgeschlossen ist.
   *
   * @return string
   */
  protected function getOverviewForward() :string {
    return "/";
  }

  /**
   * Prüft, ob der Request korrekt abgearbeitet werden kann.
   *
   * @param ServerRequestInterface $request
   * @param ResponseInterface $response
   * @param array $args
   * @param array $form
   * @return ResponseInterface|NULL wenn alles ok ist, kommt ein null zurück
   */
  protected function validateFormRequest(ServerRequestInterface $request, ResponseInterface $response, array $args, array $form, object $object) :?ResponseInterface {
    $user = $this->getCurrentUser();
    if ($user === null) {
      return $response->withStatus(StatusCodeInterface::STATUS_FORBIDDEN);
    }

    if ($object === null) {
      $view = Twig::fromRequest($request);
      return $this->withError($response, $view, "Could not found object", null, StatusCodeInterface::STATUS_NOT_FOUND);
    }

    if (count($form) === 0) {
      $view = Twig::fromRequest($request);
      return $this->withError($response, $view, "Could not initialize form", null, StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }

    // darf der User auf diesen Punkt zugreifen?
    return null;
  }
}