<?php
declare(strict_types=1);

namespace acoby\services;

use acoby\models\History;
use acoby\forms\HistoryViewItem;

abstract class AbstractHistoryViewFactory extends AbstractFactory {
  /**
   * Creates a list of viewable items in correct order
   * 
   * @param History[] $items
   * @return HistoryViewItem[]
   */
  public function createHistoryView(array $items) :array {
    $data = array();
    
    foreach ($items as $item) {
      $object = $this->getObject($item);
      $objectName = $this->getObjectName($item,$object);
      $userName = $this->getUserService();
      $entry = new HistoryViewItem($item, $object, $objectName, $userName);
      $data[$entry->day][$entry->timestamp][] = $this->customizeViewItem($entry);;
    }
    
    krsort($data);
    foreach ($data as $day => $item) {
      krsort($data[$day]);
    }
    return $data;
  }

  /**
   * Returns the correcponding object of the history
   * 
   * @param History $history
   * @return object|NULL
   */
  public abstract function getObject(History $history) :?object;
  
  /**
   * Returns the name of the object of the history
   * 
   * @param History $history
   * @param object $object
   * @return string|NULL
   */
  public abstract function getObjectName(History $history, object $object = null) :?string;
  
  /**
   * Returns the name of the creator of a history entry
   * 
   * @param History $history
   * @return string
   */
  public abstract function getCreatorNameForHistory(History $history) :string;
  
  /**
   * Customize the history view
   * 
   * @param HistoryViewItem $view
   * @return HistoryViewItem
   */
  public abstract function customizeViewItem(HistoryViewItem $view) :HistoryViewItem;
}