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
   * @param string $newValue
   * @return bool
   */
  public function isChanged($newValue = null) :bool {
    if ($this->currentValue === null && $newValue === null) return false;
    if ($this->currentValue === null || $newValue === null) return true;
    return $this->currentValue !== $newValue;
  }
  
  /**
   * Validates if the given value is valid. Depends on mandatory field, it cannot be null or it will be truncated to maxLength
   * 
   * @param string $newValue
   * @return bool
   */
  public function validate($newValue = null) :bool {
    $this->newValue = $newValue;
    
    if (Utils::isEmpty($this->newValue) && $this->mandatory) return false;
    if (isset($this->minlength) && !Utils::isEmpty($this->newValue) && strlen($this->newValue) < $this->minlength && $this->mandatory) return false;
    if (isset($this->maxlength) && !Utils::isEmpty($this->newValue) && strlen($this->newValue) > $this->maxlength) $this->newValue = substr($this->newValue,0,$this->maxlength);
    
    return true;
  }
}
