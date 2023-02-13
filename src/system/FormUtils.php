<?php
declare(strict_types=1);

namespace acoby\system;

use acoby\forms\CheckboxField;
use acoby\forms\CommentField;
use acoby\forms\EMailInputField;
use acoby\forms\InputField;
use acoby\forms\MultiSelectField;
use acoby\forms\NumberInputField;
use acoby\forms\PasswordInputField;
use acoby\forms\PhoneInputField;
use acoby\forms\SelectField;
use acoby\forms\Select2Field;
use acoby\forms\TextareaField;
use acoby\forms\TextField;
use acoby\forms\URLInputField;

class FormUtils {
  private static $instance = null;
  
  public static function getInstance() :FormUtils {
    if (self::$instance === null) self::$instance = new FormUtils();
    return self::$instance;
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
   * @return PasswordInputField
   */
  public function createPasswordField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, bool $readonly = false, int $minlength = null, int $maxlength = null, string $pattern = null) :PasswordInputField {
    $element = new PasswordInputField($tab, $name, $label, $mandatory, $readonly);
    if (isset($placeholder)) $element->placeholder = $placeholder;
    if (isset($currentValue)) $element->currentValue = $currentValue;
    if (isset($validator)) $element->validator = $validator;
    if (isset($pattern)) $element->pattern = $pattern;
    if (isset($minlength)) $element->minlength = $minlength;
    if (isset($maxlength)) $element->maxlength = $maxlength;
    return $element;
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
   * @return PhoneInputField
   */
  public function createPhoneField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, bool $readonly = false, int $minlength = null, int $maxlength = null, string $pattern = null) :PhoneInputField {
    $element = new PhoneInputField($tab, $name, $label, $mandatory, $readonly);
    if (isset($placeholder)) $element->placeholder = $placeholder;
    if (isset($currentValue)) $element->currentValue = $currentValue;
    if (isset($validator)) $element->validator = $validator;
    if (isset($pattern)) $element->pattern = $pattern;
    if (isset($minlength)) $element->minlength = $minlength;
    if (isset($maxlength)) $element->maxlength = $maxlength;
    return $element;
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
   * @return EMailInputField
   */
  public function createEMailField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, bool $readonly = false, int $minlength = null, int $maxlength = null, string $pattern = null) :EMailInputField {
    $element = new EMailInputField($tab, $name, $label, $mandatory, $readonly);
    if (isset($placeholder)) $element->placeholder = $placeholder;
    if (isset($currentValue)) $element->currentValue = $currentValue;
    if (isset($validator)) $element->validator = $validator;
    if (isset($pattern)) $element->pattern = $pattern;
    if (isset($minlength)) $element->minlength = $minlength;
    if (isset($maxlength)) $element->maxlength = $maxlength;
    return $element;
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
   * @return URLInputField
   */
  public function createURLField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, bool $readonly = false, int $minlength = null, int $maxlength = null, string $pattern = null) :URLInputField {
    $element = new URLInputField($tab, $name, $label, $mandatory, $readonly);
    if (isset($placeholder)) $element->placeholder = $placeholder;
    if (isset($currentValue)) $element->currentValue = $currentValue;
    if (isset($validator)) $element->validator = $validator;
    if (isset($pattern)) $element->pattern = $pattern;
    if (isset($minlength)) $element->minlength = $minlength;
    if (isset($maxlength)) $element->maxlength = $maxlength;
    return $element;
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
   * @return NumberInputField
   */
  public function createNumberField(string $tab, string $name, string $label, string $placeholder = null, int $currentValue = null, bool $mandatory = false, array $validator = null, int $minValue = null, int $maxValue = null, bool $readonly = false) :NumberInputField {
    $element = new NumberInputField($tab, $name, $label, $mandatory, $readonly);
    if (isset($placeholder)) $element->placeholder = $placeholder;
    if (isset($currentValue)) $element->currentValue = $currentValue;
    if (isset($validator)) $element->validator = $validator;
    if (isset($minValue)) {
      $element->minValue = $minValue;
      $element->minlength = strlen(strval($minValue));
    }
    if (isset($maxValue)) {
      $element->maxValue = $maxValue;
      $element->maxlength = strlen(strval($maxValue));
    }
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
   * @return TextField
   */
  public function createInputField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, bool $readonly = false, int $minlength = null, int $maxlength = null, string $pattern = null) :TextField {
    $element = new TextField($tab, $name, $label, $mandatory, $readonly);
    if (isset($placeholder)) $element->placeholder = $placeholder;
    if (isset($currentValue)) $element->currentValue = $currentValue;
    if (isset($validator)) $element->validator = $validator;
    if (isset($pattern)) $element->pattern = $pattern;
    if (isset($minlength)) $element->minlength = $minlength;
    if (isset($maxlength)) $element->maxlength = $maxlength;
    return $element;
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
   * @return SelectField
   */
  public function createSelectField(string $tab,string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, array $values = [], bool $readonly = false) :SelectField {
    $element = new SelectField($tab, $name, $label, $values, $mandatory, $readonly);
    if (isset($placeholder)) $element->placeholder = $placeholder;
    if (isset($currentValue)) $element->currentValue = $currentValue;
    if (isset($validator)) $element->validator = $validator;
    return $element;
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
   * @return Select2Field
   */
  public function createSelect2Field(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, string $ajax = null, bool $readonly = false) :Select2Field {
    $element = new Select2Field($tab, $name, $label, $ajax, $mandatory, $readonly);
    if (isset($placeholder)) $element->placeholder = $placeholder;
    if (isset($currentValue)) $element->currentValue = $currentValue;
    if (isset($validator)) $element->validator = $validator;
    return $element;
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
   * @return MultiSelectField
   */
  public function createMultiSelectField(string $tab, string $name, string $label, string $placeholder = null, array $currentValue = [], bool $mandatory = false, array $validator = null, array $values = [], bool $readonly = false) :MultiSelectField {
    $element = new MultiSelectField($tab, $name, $label, $values, $mandatory, $readonly);
    $element->currentValue = implode(",",$currentValue);
    if (isset($placeholder)) $element->placeholder = $placeholder;
    if (isset($validator)) $element->validator = $validator;
    return $element;
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
   * @return TextareaField
   * @deprecated use createMultilineInputField
   */
  public function createTextareaFormElement(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, int $rows = 3, bool $readonly = false) :TextareaField {
    $element = new TextareaField($tab, $name, $label, $rows, $mandatory, $readonly);
    if (isset($placeholder)) $element->placeholder = $placeholder;
    if (isset($currentValue)) $element->currentValue = $currentValue;
    if (isset($validator)) $element->validator = $validator;
    return $element;
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
   * @return TextareaField
   */
  public function createMultilineInputField(string $tab, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, int $rows = 3, bool $readonly = null) :TextareaField {
    $element = new TextareaField($tab, $name, $label, $rows, $mandatory, $readonly);
    if (isset($placeholder)) $element->placeholder = $placeholder;
    if (isset($currentValue)) $element->currentValue = $currentValue;
    if (isset($validator)) $element->validator = $validator;
    return $element;
  }
  
  /**
   * Create a text field (without input)
   *
   * @param string $tab which tab contains this element
   * @param string $name which id is used for this element
   * @param string $name which name has this element
   * @param string $label what label has this element
   * @param string $placeholder an optional placeholder in the input field
   * @param string $currentValue an optional default value
   * @return CommentField
   */
  public function createTextElement(string $tab, string $name, string $label, string $currentValue = "") :CommentField {
    $element = new CommentField($tab, $name, $label);
    $element->currentValue = $currentValue;
    return $element;
  }
  
  /**
   * Create a checkbox entry
   *
   * @param string $tab
   * @param string $name
   * @param string $label
   * @param bool $checked
   * @param array $validator
   * @return CheckboxField
   */
  public function createCheckbox(string $tab, string $name, string $label, bool $currentValue = false, array $validator = null, bool $readonly = false) :CheckboxField {
    $element = new CheckboxField($tab, $name, $label, false, $readonly);
    $element->currentValue = $currentValue;
    if (isset($validator)) $element->validator = $validator;
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
   * @return InputField
   * @deprecated please use specific implementation
   * @codeCoverageIgnore
   */
  protected function createFormElement(string $tab, string $tag, string $type, string $id, string $name, string $label, string $placeholder = null, string $currentValue = null, bool $mandatory = false, array $validator = null, array $values = null, bool $readonly = false, int $minlength = null, int $maxlength = null, string $pattern = null) :InputField {
    $element = new InputField($tab, $tag, $type, $name, $label, $mandatory, $readonly);
    $element->id = $id;
    if (isset($placeholder)) $element->placeholder = $placeholder;
    if (isset($currentValue)) $element->currentValue = $currentValue;
    if (isset($validator)) $element->validator = $validator;
    if (isset($values)) $element["values"] = $values;
    if (isset($pattern)) $element->pattern = $pattern;
    if (isset($minlength)) $element->minlength = $minlength;
    if (isset($maxlength)) $element->maxlength = $maxlength;
    return $element;
  }
  
}