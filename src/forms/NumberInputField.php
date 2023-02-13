<?php
declare(strict_types=1);

namespace acoby\forms;

/**
 * 
 */
class NumberInputField extends InputField {
  /** @var int */
  public $currentValue;
  /** @var int */
  public $minValue;
  /** @var int */
  public $maxValue;

  public function __construct(string $tab, string $name, string $label, bool $mandatory = true,  bool $readonly = false) {
    parent::__construct($tab, "input", "number", $name, $label, $mandatory, $readonly);
  }
  
  /**
   * {@inheritDoc}
   * @see \acoby\forms\InputField::validate()
   */
  public function validate($newValue = null) :bool {
    $parent = parent::validate(strval($newValue));
    if (!$parent) return $parent;
    
    $this->newValue = intval($newValue);
    
    if (isset($this->minValue) && $this->newValue < $this->minValue) return false;
    if (isset($this->maxValue) && $this->maxValue < $this->newValue) return false;
    
    return true;
  }
}
