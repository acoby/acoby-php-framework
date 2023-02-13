<?php
declare(strict_types=1);

namespace acoby\forms;

use acoby\system\Utils;

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
   * @see \acoby\forms\InputField::doPostValidate()
   */
  protected function doPostValidate() :?bool {
    if (!Utils::isEmpty($this->newValue)) {
      $newValue = intval($this->newValue);
      
      if (isset($this->minValue) && $newValue < $this->minValue) {
        $this->error = $this->label." is too small";
        return false;
      }
      if (isset($this->maxValue) && $this->maxValue < $newValue) {
        $this->error = $this->label." is too big";
        return false;
      }
    }
    return null;
  }
}
