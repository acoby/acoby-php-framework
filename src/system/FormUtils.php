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
   * @param bool $readonly define this field as readonly
   * @param int $minlength define the minimum length of the value
   * @param int $maxlength define the maximum length of this value
   * @param string $pattern define a pattern for this field
   * @return array
   * @deprecated please use a specific method
   */
  public function createInputFormElement(string $tab, string $type, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, bool $readonly = null, int $minlength = null, int $maxlength = null, string $pattern = null) :array {
    return $this->createFormElement($tab, "input", $type, $id, $name, $label, $placeholder, $currentValue, $mandatory, $validator, null, $readonly, $minlength, $maxlength, $pattern);
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
   * @param bool $readonly define this field as readonly
   * @param int $minlength define the minimum length of the value
   * @param int $maxlength define the maximum length of this value
   * @param string $pattern define a pattern for this field
   * @return array
   */
  public function createPasswordField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, bool $readonly = null, int $minlength = null, int $maxlength = null, string $pattern = null) :array {
    return $this->createFormElement($tab, "input", "password", $name, $name, $label, $placeholder, $currentValue, $mandatory, $validator, null, $readonly, $minlength, $maxlength, $pattern);
  }
  
  /**
   * Create a phone input field
   *
   * @param string $tab which tab contains this element
   * @param string $name which name has this element
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param bool $readonly define this field as readonly
   * @param int $minlength define the minimum length of the value
   * @param int $maxlength define the maximum length of this value
   * @param string $pattern define a pattern for this field
   * @return array
   */
  public function createPhoneField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, bool $readonly = null, int $minlength = null, int $maxlength = null, string $pattern = null) :array {
    return $this->createFormElement($tab, "input", "tel", $name, $name, $label, $placeholder, $currentValue, $mandatory, $validator, null, $readonly, $minlength, $maxlength, $pattern);
  }
  
  /**
   * Create a phone input field
   *
   * @param string $tab which tab contains this element
   * @param string $name which name has this element
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param bool $readonly define this field as readonly
   * @param int $minlength define the minimum length of the value
   * @param int $maxlength define the maximum length of this value
   * @param string $pattern define a pattern for this field
   * @return array
   */
  public function createEMailField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, bool $readonly = null, int $minlength = null, int $maxlength = null, string $pattern = null) :array {
    return $this->createFormElement($tab, "input", "email", $name, $name, $label, $placeholder, $currentValue, $mandatory, $validator, null, $readonly, $minlength, $maxlength, $pattern);
  }
  
  /**
   * Create a URL input field
   *
   * @param string $tab which tab contains this element
   * @param string $name which name has this element
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param bool $readonly define this field as readonly
   * @param int $minlength define the minimum length of the value
   * @param int $maxlength define the maximum length of this value
   * @param string $pattern define a pattern for this field
   * @return array
   */
  public function createURLField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, bool $readonly = null, int $minlength = null, int $maxlength = null, string $pattern = null) :array {
    return $this->createFormElement($tab, "input", "url", $name, $name, $label, $placeholder, $currentValue, $mandatory, $validator, null, $readonly, $minlength, $maxlength, $pattern);
  }
  
  /**
   * Create a number input field
   *
   * @param string $tab which tab contains this element
   * @param string $name which name has this element
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param int $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param bool $readonly define this field as readonly
   * @return array
   */
  public function createNumberField(string $tab, string $name, string $label, string $placeholder = null, int $currentValue = null, bool $mandatory = false, array $validator = null, int $minValue = null, int $maxValue = null, bool $readonly = null) :array {
    $element = array();
    $element["tab"] = $tab;
    $element["tag"] = "input";
    $element["type"] = "number";
    $element["id"] = $name;
    $element["name"] = $name;
    $element["label"] = $label;
    if (isset($placeholder)) $element["placeholder"] = $placeholder;
    if (isset($currentValue)) $element["value"] = $currentValue;
    $element["mandatory"] = $mandatory;
    if (isset($validator)) $element["validator"] = $validator;
    $element["label"] = $label;
    if (isset($minValue)) $element["minValue"] = $minValue;
    if (isset($maxValue)) $element["maxValue"] = $maxValue;
    if (isset($readonly) && $readonly) $element["readonly"] = "true";
    return $element;
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
   * @param bool $readonly define this field as readonly
   * @param int $minlength define the minimum length of the value
   * @param int $maxlength define the maximum length of this value
   * @param string $pattern define a pattern for this field
   * @return array
   */
  public function createInputField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, bool $readonly = null, int $minlength = null, int $maxlength = null, string $pattern = null) :array {
    return $this->createFormElement($tab, "input", "text", $name, $name, $label, $placeholder, $currentValue, $mandatory, $validator, null, $readonly, $minlength, $maxlength, $pattern);
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
   * @param bool $readonly define this field as readonly
   * @param int $minlength define the minimum length of the value
   * @param int $maxlength define the maximum length of this value
   * @param string $pattern define a pattern for this field
   * @param array $values a list of possible values
   * @return array
   * @deprecated please use createInputField
   */
  public function createInputTextFormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, bool $readonly = null, int $minlength = null, int $maxlength = null, string $pattern = null) :array {
    return $this->createInputFormElement($tab, "text", $id, $name, $label, $placeholder, $currentValue, $mandatory, $validator, null, $readonly, $minlength, $maxlength, $pattern);
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
   * @param array $values a list of possible values
   * @param bool $readonly define this field as readonly
   * @return array
   */
  public function createSelectField(string $tab,string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, array $values = null, bool $readonly = null) :array {
    return $this->createFormElement($tab, "select", "", $name, $name, $label, $placeholder, $currentValue, $mandatory, $validator, $values, $readonly);
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
   * @param array $values a list of allowed values
   * @return array
   * @deprecated please use createSelectField
   */
  public function createSelectFormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, array $values = null) :array {
    return $this->createFormElement($tab, "select", "", $id, $name, $label, $placeholder, $currentValue, $mandatory, $validator, $values);
  }
  
  /**
   * Create a simple dynamic select form
   *
   * @param string $tab which tab contains this element
   * @param string $id which id is used for this element
   * @param string $name which name has this element
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param string $ajax the endpoint for retrieving possible values
   * @return array
   * @deprecated please use createSelect2Field
   */
  public function createSelect2FormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, string $ajax = null) :array {
    return $this->createFormElement($tab, "select2", "", $id, $name, $label, $placeholder, $currentValue, $mandatory, $validator, ["url"=>$ajax]);
  }
  
  /**
   * Create a simple dynamic select form
   *
   * @param string $tab which tab contains this element
   * @param string $name which name has this element
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param string $ajax the endpoint for retrieving possible values
   * @return array
   */
  public function createSelect2Field(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, string $ajax = null, bool $readonly = null) :array {
    return $this->createFormElement($tab, "select2", "", $name, $name, $label, $placeholder, $currentValue, $mandatory, $validator, ["url"=>$ajax]);
  }
  
  /**
   * Create a multu select form field
   *
   * @param string $tab which tab contains this element
   * @param string $id which id is used for this element
   * @param string $name which name has this element
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param array $values a list of allowed fields
   * @return array
   * @deprecated please use createMultiSelectField
   */
  public function createMultiSelectFormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, array $currentValue = [], bool $mandatory = false, array $validator = null, array $values = null) :array {
    $value = implode(",",$currentValue);
    return $this->createFormElement($tab, "multi", "", $id, $name, $label, $placeholder, $value, $mandatory, $validator, $values);
  }
  
  /**
   * Create a multu select form field
   *
   * @param string $tab which tab contains this element
   * @param string $name which name has this element
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param array $values a list of allowed fields
   * @param bool $readonly define this field as readonly
   * @return array
   */
  public function createMultiSelectField(string $tab, string $name, string $label, string $placeholder = null, array $currentValue = [], bool $mandatory = false, array $validator = null, array $values = null, bool $readonly = null) :array {
    $value = implode(",",$currentValue);
    return $this->createFormElement($tab, "multi", "", $name, $name, $label, $placeholder, $value, $mandatory, $validator, $values, $readonly);
  }
  
  /**
   * Create a multi line text input field
   *
   * @param string $tab which tab contains this element
   * @param string $id which id is used for this element
   * @param string $name which name has this element
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param int $rows
   * @return array
   */
  public function createTextareaFormElement(string $tab, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, int $rows = 3) :array {
    return $this->createFormElement($tab, "textarea", strval($rows), $id, $name, $label, $placeholder, $currentValue, $mandatory, $validator, null);
  }
  
  /**
   * Create a multi line text input field
   *
   * @param string $tab which tab contains this element
   * @param string $name which name has this element
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @param bool $mandatory mark this field as mandatory (must be filled)
   * @param array $validator a reference to a validator that is called with new data
   * @param int $rows
   * @return array
   */
  public function createMultilineInputField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, int $rows = 3, bool $readonly = null) :array {
    return $this->createFormElement($tab, "textarea", strval($rows), $name, $name, $label, $placeholder, $currentValue, $mandatory, $validator, null, $readonly);
  }
  
  /**
   * Create a text field (without input)
   *
   * @param string $tab which tab contains this element
   * @param string $id which id is used for this element
   * @param string $name which name has this element
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @return array
   */
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
   * @param array $validator
   * @return array
   */
  public function createCheckbox(string $tab, string $name, string $label, bool $currentValue = false, array $validator = null, bool $readonly = null) :array {
    $element = array();
    $element["tab"] = $tab;
    $element["tag"] = "checkbox";
    $element["type"] = "checkbox";
    $element["id"] = $name;
    $element["name"] = $name;
    $element["label"] = $label;
    $element["value"] = $currentValue;
    if (isset($validator)) $element["validator"] = $validator;
    if (isset($readonly) && $readonly) $element["readonly"] = "true";
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
  protected function createFormElement(string $tab, string $tag, string $type, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, array $values = null, bool $readonly = null, int $minlength = null, int $maxlength = null, string $pattern = null) :array {
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
    if (isset($readonly) && $readonly) $element["readonly"] = "true";
    if (isset($pattern)) $element["pattern"] = $pattern;
    if (isset($minlength)) $element["minlength"] = $minlength;
    if (isset($maxlength)) $element["maxlength"] = $maxlength;
    return $element;
  }
  
}