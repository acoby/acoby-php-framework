<?php
declare(strict_types=1);

namespace acoby\services;

use DateTime;
use acoby\models\History;

class HistoryFactory {
  private static $instance = null;
  
  /** */
  public static function setInstance(HistoryService $instance) :void {
    self::$instance = $instance;
  }
  
  /** */
  public static function getInstance() :HistoryService {
    return self::$instance;
  }
  
  /**
   *
   * @param object $object
   * @param array $items
   * @param string $title
   * @param string $name
   * @return array
   */
  public function createHistory(object $object, array $items, string $title, string $name) :array {
    $data = array();
    $addMessage = false;
    $modifiedMessage = false;
    
    foreach ($items as $item) {
      $response = $this->addHistoryEntry($data,$item,$title,$name);
      
      if (isset($response["add"])) $addMessage = true;
      if (isset($response["modified"])) $modifiedMessage = true;
    }
    
    if (!$addMessage) {
      $day = (new DateTime($object->created))->format('Y-m-d');
      $time = (new DateTime($object->created))->format('H:i');
      $timestamp = (new DateTime($object->created))->getTimestamp();
      $data[$day][$timestamp][] = $this->createHistoryEntry($time,$title." created","fa-user","The ".strtolower($title)." ".$name." was created");
    }
    
    if (!$modifiedMessage) {
      $day = (new DateTime($object->changed))->format('Y-m-d');
      $time = (new DateTime($object->changed))->format('H:i');
      $timestamp = (new DateTime($object->changed))->getTimestamp();
      $data[$day][$timestamp][] = $this->createHistoryEntry($time,$title."Host last updated","fa-user","The ".strtolower($title)." ".$name." was last changed");
    }
    
    
    krsort($data);
    foreach ($data as $day => $item) {
      krsort($data[$day]);
    }
    return $data;
  }
  
  /**
   *
   * @param array $data
   * @param History $item
   * @param string $title
   * @param string $name
   * @return array
   */
  protected function addHistoryEntry(array &$data, History $item, string $title, string $name, string $user = null) :array {
    $response = array();
    
    $date = (new DateTime($item->created))->format('Y-m-d');
    $time = (new DateTime($item->created))->format('H:i');
    $timestamp = (new DateTime($item->created))->getTimestamp();
    
    $subject = $title;
    $creator = " by Unknown";
    if ($user !== null) $creator = " by ".$user;
    
    if ($item->mode === HistoryService::MODE_ADD) {
      $response["add"] = true;
      $subject.= " created";
    } else if ($item->mode === HistoryService::MODE_CHANGED) {
      $response["modified"] = true;
      $subject.= " changed";
    } else if ($item->mode === HistoryService::MODE_DELETED) {
      $subject.= " deleted";
    } else {
      $response["modified"] = true;
      $subject.= " modified";
    }
    
    $data[$date][$timestamp][] = $this->createHistoryEntry($time,$subject,"fa-user",$item->message.$creator);
    
    return $response;
  }
  
  /**
   *
   * @param string $time
   * @param string $title
   * @param string $icon
   * @param string $content
   * @param string $footer
   * @return array
   */
  protected function createHistoryEntry(string $time, string $title, string $icon, string $content = null, string $footer = null) :array {
    $item = array();
    $item["icon"] = $icon;
    $item["time"] = $time;
    $item["title"] = $title;
    if ($content !== null) $item["content"] = $content;
    if ($footer !== null) $item["footer"] = $footer;
    return $item;
  }
  
}