<?php
declare(strict_types=1);

namespace acoby\models;

abstract class AbstractSearch {
  // @var string|NULL
  public $created;

  // @var string|NULL
  public $changed;

  // @var string|NULL
  public $deleted;

  // @var integer|null
  public $offset;

  // @var integer|null
  public $limit;

  // @var bool|NULL
  public $expand;

  // @var string|NULL
  public $searcherId;


  /**
   * verifiziert ein Objekt
   */
  public function verify() :void {
    if (!isset($this->expand)) $this->expand = FALSE;
    if (!isset($this->offset)) $this->offset = 0;
    if (!isset($this->limit)) $this->limit = 100;
  }
}