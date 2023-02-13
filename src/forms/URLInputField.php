<?php
declare(strict_types=1);

namespace acoby\forms;

use acoby\system\Utils;

/**
 * 
 */
class URLInputField extends InputField {
  /** @var string */
  public $currentValue;

  public function __construct(string $tab, string $name, string $label, bool $mandatory = true,  bool $readonly = false) {
    parent::__construct($tab, "input", "url", $name, $label, $mandatory, $readonly);
  }
  
  /**
   * {@inheritDoc}
   * @see \acoby\forms\InputField::validate()
   */
  public function validate($newValue = null) :bool {
    $parent = parent::validate($newValue);
    
    if (!Utils::isEmpty($this->newValue) && !filter_var($this->newValue, FILTER_VALIDATE_URL)) {
      return false;
    }
    
    return $parent;
  }
}
