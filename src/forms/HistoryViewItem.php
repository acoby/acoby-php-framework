<?php
declare(strict_types=1);

namespace acoby\forms;

use DateTime;
use acoby\models\History;
use acoby\models\HistoryMode;

/**
 * Presents a history view item
 * 
 * @author Thoralf Rickert-Wendt
 */
class HistoryViewItem {
  public $historyId;
  public $history;
  public $objectType;
  public $objectId;
  public $object;
  public $mode;
  public $day;
  public $time;
  public $timestamp;
  public $icon;
  public $title;
  public $content;
  public $footer;
  
  /**
   * Returns a filled item with the given 
   * 
   * @param History $history
   * @param string $username
   * @param object $object
   * @param string $objectName
   * @param string $icon
   */
  public function __construct(History $history, object $object, string $objectName, string $username = "Unknown", string $icon = "fa-user") {
    $dateTime = new DateTime($history->created);
    
    $this->historyId = $history->externalId;
    $this->history = $history;
    
    $this->objectId = $history->objectId;
    $this->objectType = $history->objectType;
    $this->object = $object;
    
    $this->mode = $history->mode;
    $this->day = $dateTime->format('Y-m-d');
    $this->time = $dateTime->format('H:s');
    $this->timestamp = $dateTime->getTimestamp();
    
    $this->icon = $icon;
    $this->title = $objectName." ".HistoryMode::toString($this->mode)." by ".$username;
    $this->content = $history->message;
    $this->footer = null;
  }
}