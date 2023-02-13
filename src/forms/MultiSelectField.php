<?php
declare(strict_types=1);

namespace acoby\forms;

/**
 * 
 */
class MultiSelectField extends InputField {
  /** @var string */
  public $currentValue;
  /** @var string[] */
  public $availableValues;
  
  /**
   * @param string $tab
   * @param string $name
   * @param string $label
   * @param string[] $availableValues
   * @param bool $mandatory
   * @param bool $readonly
   */
  public function __construct(string $tab, string $name, string $label, array $availableValues, bool $mandatory = true,  bool $readonly = false) {
    parent::__construct($tab, "multi", "", $name, $label, $mandatory, $readonly);
    $this->availableValues = $availableValues;
    $this->currentValue = array();
  }
}
