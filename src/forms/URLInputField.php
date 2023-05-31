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
   * @see InputField::doPostValidate
   */
  protected function doPostValidate() :?bool {
    if (!Utils::isEmpty($this->newValue) && !filter_var($this->newValue, FILTER_VALIDATE_URL)) {
      $this->error = $this->label." is not valid";
      return false;
    }
    return null;
  }
}
