<?php
declare(strict_types=1);

namespace acoby\models;

abstract class AbstractSearch {
  /**
   * @OA\Property(type="string", description="Time of creation")
   * @var string|NULL
   */
  public $created;
  
  /**
   * @OA\Property(type="string", description="Time of last change")
   * @var string|NULL
   */
  public $changed;
  
  /**
   * @OA\Property(type="string", description="Time of deletion")
   * @var string|NULL
   */
  public $deleted;
  
  /**
   * @OA\Property(type="integer", description="offset of the result set, which should be returned", default="0", example="0")
   * @var integer|null
   */
  public $offset;
  /**
   * @OA\Property(type="integer", description="limit the result set to the given number", default="100", example="100")
   * @var integer|null
   */
  public $limit;
  
  /**
   * @OA\Property(type="boolean", description="expand the complex object in response", default="false", example="false")
   * @var bool|NULL
   */
  public $expand;
  
  /**
   * @OA\Property(type="boolean", description="Reverse the order", default="false", example="false")
   * @var bool|NULL
   */
  public $reverse;
  
  /**
   * @var string|NULL
   */
  public $searcherId;

  /**
   * verifiziert ein Objekt
   */
  public function verify() :void {
    if (!isset($this->expand)) $this->expand = FALSE;
    if (!isset($this->reverse)) $this->reverse = FALSE;
    if (!isset($this->offset)) $this->offset = 0;
    if (!isset($this->limit)) $this->limit = 100;
  }
}