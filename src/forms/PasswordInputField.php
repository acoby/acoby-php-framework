<?php
declare(strict_types=1);

namespace acoby\forms;

/**
 * 
 */
class PasswordInputField extends InputField {
  /** @var string */
  public $currentValue;

  public function __construct(string $tab, string $name, string $label, bool $mandatory = true,  bool $readonly = false) {
    parent::__construct($tab, "input", "password", $name, $label, $mandatory, $readonly);
  }
}
