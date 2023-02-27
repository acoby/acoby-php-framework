<?php
declare(strict_types=1);

namespace acoby\forms;

use acoby\services\ConfigService;
use acoby\system\Utils;

/**
 * 
 */
class AvatarSelectField extends InputField {
  /** @var string[] */
  public $currentValue;
  /** @var string[] */
  public $availableValues;
  
  /**
   * @param string $tab
   * @param string $name
   * @param string $label
   * @param string[] $availableValues
   * @param bool $mandatory
   * @param bool $readonly
   */
  public function __construct(string $tab, string $name, string $label, string $path, bool $mandatory = true,  bool $readonly = false) {
    parent::__construct($tab, "select", "avatar", $name, $label, $mandatory, $readonly);
    $this->availableValues = $this->getAvatarItems($path);
  }
  
  /**
   * Scan given directory (docroot based) for images.
   * 
   * @param string $path
   * @return string[] array of possible values
   */
  protected function getAvatarItems(string $path) :array {
    $items = array();
    $imagePath = ConfigService::getString("basedir")."/public".$path;
    if (!is_dir($imagePath)) return $items;
    $files = scandir($imagePath);
    foreach ($files as $file) {
      if (Utils::startsWith($file, ".")) continue;
      if (!Utils::endsWith($file, ".png")) continue; // we do accept PNG files only at the moment
      $item = array();
      $item["value"] = $file;
      $item["thumbnail"] = $path."/".$file;
      $item["title"] = substr($file,0,strrpos($file,"."));
      $items[] = $item;
    }
    return $items;
  }
}
