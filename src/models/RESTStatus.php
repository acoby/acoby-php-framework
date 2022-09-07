<?php
declare(strict_types=1);

namespace acoby\models;

/**
 * @OA\Schema(
 *   schema="RESTStatus",
 *   type="object",
 *   required={"code"}
 * )
 */
class RESTStatus {
  /**
   * @OA\Property(type="integer")
   * @var integer|null
   */
  public $code;
  /**
   * @OA\Property(ref="#/components/schemas/RESTError")
   * @var RESTError|null
   */
  public $error;
  /**
   * @OA\Property(ref="#/components/schemas/RESTResult")
   * @var RESTResult|null
   */
  public $result;
  /**
   * @OA\Property(type="integer")
   * @var integer|null
   */
  public $offset;
  /**
   * @OA\Property(type="integer")
   * @var integer|null
   */
  public $limit;
  /**
   * @OA\Property(type="integer")
   * @var integer|null
   */
  public $total;
}
