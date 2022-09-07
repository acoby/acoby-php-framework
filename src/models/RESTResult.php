<?php
declare(strict_types=1);

namespace acoby\models;

/**
 * @OA\Schema(
 *   schema="RESTResult",
 *   type="object",
 *   required={"message"}
 * )
 */
class RESTResult {
  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $message;
}
