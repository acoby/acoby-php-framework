<?php
declare(strict_types=1);

namespace acoby\system;

class FormUtils {
  private static $instance = null;

  public static function getInstance() :FormUtils {
    if (self::$instance === null) self::$instance = new FormUtils();
    return self::$instance;
  }

  /**
   * create a standard text input field
   * 
   * @param string $tab which tab contains this element
   * @param string $type
   * @param string $id
   * @param string $name which name has this element 
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param array $values
   * @return array
   */
  public function createInputFormElement(string $tab, string $type, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, array $values = null) :array {
    return $this->createFormElement($tab, "input", $type, $id, $name, $label, $placeholder, $currentValue, $mandatory, $validator, $values);
  }
  
  /**
   * Create a password input field
   * 
   * @param string $tab which tab contains this element
   * @param string $name which name has this element 
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @return array
   */
  public function createPasswordField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null) :array {
    return $this->createFormElement($tab, "input", "password", $name, $name, $label, $placeholder, $currentValue, $mandatory, $validator);
  }
  
  /**
   * Create a text input form
   * 
   * @param string $tab which tab contains this element
   * @param string $name which name has this element 
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @return array
   */
  public function createInputField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null) :array {
    return $this->createInputTextFormElement($tab, $name, $name, $label, $placeholder, $currentValue, $mandatory, $validator);
  }
  

  /**
   * Create a text input form
   * 
   * @param string $tab which tab contains this element
   * @param string $id which id is used for this element
   * @param string $name which name has this element 
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param array $values
   * @return array
   */
  public function createInputTextFormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, array $values = null) :array {
    return $this->createInputFormElement($tab, "text", $id, $name, $label, $placeholder, $currentValue, $mandatory, $validator, $values);
  }

  /**
   * Create a simple static select form
   * 
   * @param string $tab which tab contains this element
   * @param string $name which name has this element 
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param array $values
   * @return array
   */
  public function createSelectField(string $tab,string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, array $values = null) :array {
    return $this->createSelectFormElement($tab, $name, $name, $label, $placeholder, $currentValue, $mandatory, $validator, $values);
  }
  
  /**
   * Create a simple static select form
   * 
   * @param string $tab which tab contains this element
   * @param string $id which id is used for this element
   * @param string $name which name has this element 
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param array $values
   * @return array
   */
  public function createSelectFormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, array $values = null) :array {
    return $this->createFormElement($tab, "select", "", $id, $name, $label, $placeholder, $currentValue, $mandatory, $validator, $values);
  }
  
  public function createSelect2FormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, string $ajax = null) :array {
    return $this->createFormElement($tab, "select2", "", $id, $name, $label, $placeholder, $currentValue, $mandatory, $validator, ["url"=>$ajax]);
  }

  public function createMultiSelectFormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, array $currentValue = [], bool $mandatory = false, array $validator = null, array $values = null) :array {
    $value = implode(",",$currentValue);
    return $this->createFormElement($tab, "multi", "", $id, $name, $label, $placeholder, $value, $mandatory, $validator, $values);
  }

  public function createTextareaFormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, int $rows = 3) :array {
    return $this->createFormElement($tab, "textarea", strval($rows), $id, $name, $label, $placeholder, $currentValue, $mandatory, $validator, null);
  }

  public function createTextElement(string $tab, string $id, string $label, string $currentValue = "") :array {
    return $this->createFormElement($tab, "text", "", $id, $id, $label, $label, $currentValue, false, null, null);
  }
  
  /**
   * Create a checkbox entry
   * 
   * @param string $tab
   * @param string $name
   * @param string $label
   * @param bool $checked
   * @param bool $mandatory
   * @param array $validator
   * @return array
   */
  public function createCheckbox(string $tab, string $name, string $label, bool $currentValue = false, bool $mandatory = false, array $validator = null) :array {
    $element = array();
    $element["tab"] = $tab;
    $element["tag"] = "checkbox";
    $element["type"] = "checkbox";
    $element["id"] = $name;
    $element["name"] = $name;
    $element["label"] = $label;
    $element["value"] = $currentValue;
    $element["mandatory"] = $mandatory;
    if (isset($validator)) $element["validator"] = $validator;
    return $element;
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
  protected function createFormElement(string $tab, string $tag, string $type, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, array $values = null) :array {
    $element = array();
    $element["tab"] = $tab;
    $element["tag"] = $tag;
    $element["type"] = $type;
    $element["id"] = $id;
    $element["name"] = $name;
    $element["label"] = $label;
    if (isset($placeholder)) $element["placeholder"] = $placeholder;
    if (isset($currentValue)) $element["value"] = $currentValue;
    $element["mandatory"] = $mandatory;
    if (isset($validator)) $element["validator"] = $validator;
    if (isset($values)) $element["values"] = $values;
    return $element;
  }

}