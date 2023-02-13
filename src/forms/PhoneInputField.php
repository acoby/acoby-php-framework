<?php
declare(strict_types=1);

namespace acoby\forms;

/**
 * 
 */
class PhoneInputField extends InputField {
  /** @var string */
  public $currentValue;

  public function __construct(string $tab, string $name, string $label, bool $mandatory = true,  bool $readonly = false) {
    parent::__construct($tab, "input", "tel", $name, $label, $mandatory, $readonly);
  }
}
