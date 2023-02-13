<?php
declare(strict_types=1);

namespace acoby\forms;

use acoby\system\Utils;

/**
 * 
 */
class CheckboxField extends InputField {
  /** @var bool */
  public $currentValue;
    
  public function __construct(string $tab, string $name, string $label, bool $readonly = false) {
    parent::__construct($tab, "checkbox", "checkbox", $name, $label, false, $readonly);
    $this->currentValue = false;
  }

  /**
   * {@inheritDoc}
   * @see \acoby\forms\InputField::doPreValidate()
   */
  protected function doPreValidate() :?bool {
    $this->newValue = Utils::bool2str(Utils::asBool($this->newValue));
    return null;
  }
}
