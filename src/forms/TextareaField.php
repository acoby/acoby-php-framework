<?php
declare(strict_types=1);

namespace acoby\forms;

/**
 * 
 */
class TextareaField extends InputField {
  /** @var string */
  public $currentValue;
  
  public function __construct(string $tab, string $name, string $label, int $rows = 3, bool $mandatory = true,  bool $readonly = false) {
    parent::__construct($tab, "textarea", strval($rows), $name, $label, $mandatory, $readonly);
  }
}
