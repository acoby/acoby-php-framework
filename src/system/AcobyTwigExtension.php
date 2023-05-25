<?php
namespace acoby\system;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use acoby\services\ConfigService;
use acoby\services\AbstractFactory;
use acoby\models\AbstractUser;

/**
 * 
 * @author trw
 */
class AcobyTwigExtension extends AbstractExtension {
  /**
   * @return TwigFunction[]
   */
  public function getFunctions() :array {
    return [
        new TwigFunction('get_config', [ConfigService::class,'getString']),
        new TwigFunction('get_menu', [AcobyTwigExtension::class,'getMenu']),
        new TwigFunction('get_current_user', [AcobyTwigExtension::class,'getCurrentUser']),
        new TwigFunction('has_min_role', [AcobyTwigExtension::class,'hasMinRole']),
        new TwigFunction('dump_var', [AcobyTwigExtension::class,'dump_var']),
        new TwigFunction('get_value', [AcobyTwigExtension::class,'getValue']),
    ];
  }

  public static function getValue($array, $key) :string {
    if (isset($array[$key])) return $array[$key];
    return $key;
  }

  public static function dump_var($value) :string {
    $output = "";
    if ($value !== null) $output = print_r($value,true);
    error_log("dump(): ".$output);
    return $output;
  }

  public static function getMenu() :array {
    return ConfigService::getArray("ui_menu",[]);
  }

  public static function hasMinRole(string $role) :bool {
    $user = SessionManager::getInstance()->getUser(new AbstractUser());
    if ($user === null) return false;
    return AbstractFactory::getUserService()->hasMinRole($user, $role);
  }

  public static function getCurrentUser() :?AbstractUser {
    return SessionManager::getInstance()->getUser(new AbstractUser());
  }
}