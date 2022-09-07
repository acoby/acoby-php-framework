<?php
declare(strict_types=1);

namespace acoby\models;

/**
 * @OA\Schema(
 *   schema="HistorySearch",
 *   type="object"
 * )
 */
class HistorySearch {
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
   * @var string|NULL
   */
  public $searcherId;

  /**
   * @OA\Property(type="string",format="uuid")
   * @var string|null
   */
  public $externalId;

  /**
   * @OA\Property(type="string",format="uuid")
   * @var string|null
   */
  public $creatorId;

  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $created;

  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $objectId;

  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $objectType;

  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $message;


  /**
   * verifiziert ein Objekt
   */
  public function verify() :void {
    if (!isset($this->expand)) $this->expand = FALSE;
    if (!isset($this->offset)) $this->offset = 0;
    if (!isset($this->limit)) $this->limit = 100;
  }
}