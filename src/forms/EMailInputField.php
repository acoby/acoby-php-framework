<?php
declare(strict_types=1);

namespace acoby\forms;

use acoby\system\Utils;

/**
 * 
 */
class EMailInputField extends InputField {
  /** @var string */
  public $currentValue;

  public function __construct(string $tab, string $name, string $label, bool $mandatory = true,  bool $readonly = false) {
    parent::__construct($tab, "input", "email", $name, $label, $mandatory, $readonly);
  }
  
  /**
   * {@inheritDoc}
   * @see \acoby\forms\InputField::validate()
   */
  public function validate($newValue = null) :bool {
    $parent = parent::validate($newValue);
    
    if (!Utils::isEmpty($this->newValue) && !filter_var($this->newValue, FILTER_VALIDATE_EMAIL)) {
      return false;
    }
    
    return $parent;
  }
}
