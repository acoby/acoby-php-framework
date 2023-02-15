<?php
declare(strict_types=1);

namespace acoby\models;

/**
 * @OA\Schema(
 *   schema="HistorySearch",
 *   type="object",
 *   required={"offset","limit"},
 *   @OA\Property(property="created", type="string", description="Time of creation"),
 *   @OA\Property(property="changed", type="string", description="Time of last change"),
 *   @OA\Property(property="deleted", type="string", description="Time of deletion"),
 *   @OA\Property(property="offset", type="integer", description="offset of the result set, which should be returned", default="0", example="0"),
 *   @OA\Property(property="limit", type="integer", description="limit the result set to the given number", default="100", example="100"),
 *   @OA\Property(property="expand", type="boolean", description="expand the complex object in response", default="false", example="false"),
 *   @OA\Property(property="reverse", type="boolean", description="Reverse the order", default="false", example="false")
 * )
 */
class HistorySearch extends AbstractSearch {
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
   * @OA\Property(type="int")
   * @var int|null
   */
  public $mode;
  
  /**
   * @OA\Property(type="string",format="uuid")
   * @var string|null
   */
  public $objectId;

  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $objectType;
}