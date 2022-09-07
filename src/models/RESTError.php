<?php
declare(strict_types=1);

namespace acoby\models;

/**
 * @OA\Schema(
 *   schema="RESTError",
 *   type="object",
 *   required={"message"}
 * )
 */
class RESTError {
  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $message;
  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $file;
  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $line;
  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $trace;
}
