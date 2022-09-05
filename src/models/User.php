<?php
namespace acoby\models;

/**
 * @OA\Schema(
 *   schema="User",
 *   type="object",
 *   required={"username","name"}
 * )
 */
class User {
  /**
   * @OA\Property(type="string",format="uuid")
   * @var string|null
   */
  public $externalId;

  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $username;
  /**
   * @OA\Property(type="string",format="password")
   * @var string|null
   */
  public $password;
  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $roles;
  /**
   * @OA\Property(type="string",format="uuid")
   * @var string|null
   */
  public $customerId;
  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $department;
  /**
   * @OA\Property(ref="#/components/schemas/Gender")
   * @var string|null
   */
  public $gender;
  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $title;
  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $firstName;
  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $lastName;
  /**
   * @OA\Property(type="string",format="email")
   * @var string|null
   */
  public $email;
    /**
   * @OA\Property(type="string",format="email")
   * @var string|null
   */
  public $email_notification;
/**
   * @OA\Property(type="string",format="phone")
   * @var string|null
   */
  public $phone;
  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $avatar;
  /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $pubkey;
    /**
   * @OA\Property(type="string")
   * @var string|null
   */
  public $shadow;
  /**
   * @OA\Property(type="string",format="date-time")
   * @var string|null
   */
  public $created;
  /**
   * @OA\Property(type="string",format="date-time")
   * @var string|null
   */
  public $changed;
  /**
   * @OA\Property(type="string",format="date-time")
   * @var string|null
   */
  public $deleted;
  /**
   * @OA\Property(type="string",format="uuid")
   * @var string|null
   */
  public $creatorId;
}
