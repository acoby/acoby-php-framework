<?php
declare(strict_types=1);

namespace acoby\models;

use DateTime;
use acoby\exceptions\IllegalArgumentException;
use acoby\services\HistoryService;

/**
 * @OA\Schema(
 *   schema="History",
 *   type="object",
 *   required={"created","objectId","objectType"}
 * )
 */
class History {
  const TABLE_NAME = "history";
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
   * @OA\Property(type="string",format="date-time")
   * @var string|null
   */
  public $created;

  /**
   * @OA\Property(type="integer")
   * @var integer|null
   */
  public $mode;

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
   * verifies an Object
   *
   * @param boolean $isNew
   * @throws IllegalArgumentException::
   * @return boolean
   */
  public function verify(bool $isNew = true) :bool {
    if (!$isNew) {
      if (!$this->externalId) throw new IllegalArgumentException("Unknown external id");
    }

    if (!is_string($this->created)) throw new IllegalArgumentException("created is not set");
    if (!is_string($this->objectId)) throw new IllegalArgumentException("objectId is not set");
    if (!is_string($this->objectType)) throw new IllegalArgumentException("objectType is not set");
    if (!is_int($this->mode)) throw new IllegalArgumentException("mode is not set");
    if (!is_string($this->message)) throw new IllegalArgumentException("message is not set");
    if (!is_string($this->creatorId) && $this->objectType !== HistoryService::TYPE_USER) throw new IllegalArgumentException("creatorId is not set");

    return true;
  }

  /**
   *
   * @param History $host
   * @return History|NULL
   */
  public function reform(bool $expand = false) :History {
    if (isset($this->created)) $this->created = (new DateTime($this->created))->format('c');
    unset($this->id);
    return $this;
  }
}