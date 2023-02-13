<?php
declare(strict_types=1);

namespace acoby\forms;

/**
 * 
 */
class Select2Field extends InputField {
  /** @var string */
  public $currentValue;
  /** @var string[] */
  public $availableValues;
  /** @var string */
  public $url;
  
  public function __construct(string $tab, string $name, string $label, string $ajaxUrl, bool $mandatory = true,  bool $readonly = false) {
    parent::__construct($tab, "select2", "", $name, $label, $mandatory, $readonly);
    $this->url = $ajaxUrl;
    $this->values = ["url"=>$ajaxUrl];
  }
}
