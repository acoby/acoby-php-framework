<?php
declare(strict_types=1);

namespace acoby\models;

use acoby\exceptions\IllegalArgumentException;
use Exception;
use Ramsey\Uuid\Uuid;
use acoby\system\Utils;

/**
 * @OA\Schema(
 *   schema="History",
 *   type="object"
 * )
 */
class History {
  const TABLE_NAME = "history";
  /**
   * @OA\Property(type="string",format="uuid", readOnly=true)
   * @var string|null
   */
  public $externalId;
  
  /**
   * @OA\Property(type="string",format="date-time", readOnly=true)
   * @var string|null
   */
  public $created;
  
  /**
   * @OA\Property(type="string",format="uuid", readOnly=true)
   * @var string|null
   */
  public $creatorId;
  
  /**
   * @var AbstractUser|null
   */
  public $creator;
  
  /**
   * @OA\Property(type="int", readOnly=true)
   * @var int|null
   */
  public $mode;
  
  /**
   * @OA\Property(type="string",format="uuid", readOnly=true)
   * @var string|null
   */
  public $objectId;
  
  /**
   * @OA\Property(type="string", enum={"user","customer","peer", "network"}, readOnly=true)
   * @var string|null
   */
  public $objectType;
  
  /**
   * @var object|null
   */
  public $object;
  
  /**
   * @OA\Property(type="string", readOnly=true)
   * @var string|null
   */
  public $message;

  /**
   * verify the incoming object.
   * @throws IllegalArgumentException
   * @throws IllegalArgumentException
   * @throws IllegalArgumentException
   * @throws IllegalArgumentException
   * @throws IllegalArgumentException
   */
  public function verify(bool $isNew = true) :bool {
    if ($isNew) {
      if (!isset($this->externalId)) $this->externalId = Uuid::uuid4()->toString();
      if (!isset($this->created)) $this->created = date('Y-m-d H:i:s');
    } else {
      if (!is_string($this->externalId) || !Uuid::isValid($this->externalId)) throw new IllegalArgumentException("externalId is not set or invalid");
      if (!is_string($this->created) || Utils::isDateTime($this->created)) throw new IllegalArgumentException("created is not set or invalid");
    }
    
    if (!isset($this->mode) || !HistoryMode::exists($this->mode)) throw new IllegalArgumentException("mode is not set or invalid");
    if (!is_string($this->objectId) || !Uuid::isValid($this->objectId)) throw new IllegalArgumentException("objectId is not set or invalid");
    if (!is_string($this->objectType)) throw new IllegalArgumentException("objectType is not set");
    
    return true;
  }

  /**
   * ändert das Format.
   *
   * @param bool $expand
   * @return History|NULL
   * @throws Exception
   */
  public function reform(bool $expand = false) :History {
    $this->created = Utils::getJSONDateTime($this->created);
    
    unset($this->id);
    return $this;
  }
}