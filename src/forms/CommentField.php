<?php
declare(strict_types=1);

namespace acoby\forms;

/**
 * 
 */
class CommentField extends InputField {
  /** @var string */
  public $currentValue;
  
  public function __construct(string $tab, string $name, string $label) {
    parent::__construct($tab, "text", "", $name, $label, false, true);
  }
}
