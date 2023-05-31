<?php
declare(strict_types=1);

namespace acoby\forms;

use acoby\system\Utils;

/**
 * 
 */
class InputField {
  /** @var string */
  public $tab;
  /** @var string */
  public $tag;
  /** @var string */
  public $type;
  /** @var string */
  public $id;
  /** @var string */
  public $name;
  /** @var string */
  public $label;
  /** @var string */
  public $placeholder;
  /** @var string */
  public $currentValue;
  /** @var string */
  public $newValue;
  /** @var bool */
  public $mandatory;
  /** @var array[] */
  public $validator;
  /** @var bool */
  public $readonly;
  /** @var string */
  public $minlength;
  /** @var string */
  public $maxlength;
  /** @var string */
  public $pattern;
  /** @var string */
  public $error;
  
  public function __construct(string $tab, string $tag, string $type, string $name, string $label, bool $mandatory = true,  bool $readonly = false) {
    $this->tab = $tab;
    $this->tag = $tag;
    $this->type = $type;
    $this->id = $name;
    $this->name = $name;
    $this->label = $label;
    $this->readonly = $readonly;
    $this->mandatory = $mandatory;
  }

  /**
   * Checks, if newValue and currentvalue differ
   * 
   * @return bool true, wenn newValue and currentValue differ
   */
  public function isChanged() :bool {
    if ($this->currentValue === null && $this->newValue === null) return false;
    if ($this->currentValue === null || $this->newValue === null) return true;
    return $this->currentValue !== $this->newValue;
  }

  /**
   * Validates if the given value is valid. Depends on mandatory field, it cannot be null, or it will be truncated to maxLength
   *
   * @param object $object
   * @return bool
   */
  public function validate(object $object) :bool {
    $valid = $this->doPreValidate();
    if ($valid !== null) return $valid;
    
    if (Utils::isEmpty($this->newValue) && $this->mandatory) {
      $this->error = $this->label." must be defined";
      return false;
    }
    if (isset($this->minlength) && !Utils::isEmpty($this->newValue) && strlen(strval($this->newValue)) < $this->minlength && $this->mandatory) {
      $this->error = $this->label." is too short";
      return false;
    }
    if (isset($this->maxlength) && !Utils::isEmpty($this->newValue) && strlen(strval($this->newValue)) > $this->maxlength) {
      $this->error = $this->label." is too long";
      return false;
    }

    $valid = $this->doPostValidate();
    if ($valid !== null) return $valid;
    
    if (isset($this->validator)) {
      return call_user_func_array($this->validator,[$object,$this]);
    }
    return true;
  }
  
  /**
   *
   */
  protected function doPreValidate() :?bool {
    // do nothing
    return null;
  }

  /**
   *
   */
  protected function doPostValidate() :?bool {
    // do nothing
    return null;
  }
}
