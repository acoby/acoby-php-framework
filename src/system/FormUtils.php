<?php
declare(strict_types=1);

namespace acoby\system;

class FormUtils {
  private static $instance = null;

  public static function getInstance() :FormUtils {
    if (self::$instance === null) self::$instance = new FormUtils();
    return self::$instance;
  }

  public function createInputFormElement(string $tab, string $type, string $id, string $name, string $label, string $placeholder = null, string $value = null, bool $mandatory = false, array $validator = null, array $values = null) :array {
    return $this->createFormElement($tab, "input", $type, $id, $name, $label, $placeholder, $value, $mandatory, $validator, $values);
  }

  public function createInputTextFormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, string $value = null, bool $mandatory = false, array $validator = null, array $values = null) :array {
    return $this->createInputFormElement($tab, "text", $id, $name, $label, $placeholder, $value, $mandatory, $validator, $values);
  }

  public function createSelectFormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, string $value = null, bool $mandatory = false, array $validator = null, array $values = null) :array {
    return $this->createFormElement($tab, "select", "", $id, $name, $label, $placeholder, $value, $mandatory, $validator, $values);
  }

  public function createSelect2FormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, string $value = null, bool $mandatory = false, array $validator = null, string $ajax = null) :array {
    return $this->createFormElement($tab, "select2", "", $id, $name, $label, $placeholder, $value, $mandatory, $validator, ["url"=>$ajax]);
  }

  public function createMultiSelectFormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, array $value = [], bool $mandatory = false, array $validator = null, array $values = null) :array {
    $value = implode(",",$value);
    return $this->createFormElement($tab, "multi", "", $id, $name, $label, $placeholder, $value, $mandatory, $validator, $values);
  }

  public function createTextareaFormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, string $value = null, bool $mandatory = false, array $validator = null, int $rows = 3) :array {
    return $this->createFormElement($tab, "textarea", "".$rows, $id, $name, $label, $placeholder, $value, $mandatory, $validator, null);
  }

  public function createTextElement(string $tab, string $id, string $label, string $value = "") :array {
    return $this->createFormElement($tab, "text", "", $id, $id, $label, $label, $value, false, null, null);
  }

  /**
   * Erzeugt ein Form Objekt
   *
   * @param string $tag
   * @param string $type
   * @param string $id
   * @param string $name
   * @param string $label
   * @param string $placeholder
   * @param string $value
   * @param array $validator
   * @return array
   */
  protected function createFormElement(string $tab, string $tag, string $type, string $id, string $name, string $label, string $placeholder = null, string $value = null, bool $mandatory = false, array $validator = null, array $values = null) :array {
    $element = array();
    $element["tab"] = $tab;
    $element["tag"] = $tag;
    $element["type"] = $type;
    $element["id"] = $id;
    $element["name"] = $name;
    $element["label"] = $label;
    if (isset($placeholder)) $element["placeholder"] = $placeholder;
    if (isset($value)) $element["value"] = $value;
    $element["mandatory"] = $mandatory;
    if (isset($validator)) $element["validator"] = $validator;
    if (isset($values)) $element["values"] = $values;
    return $element;
  }

}