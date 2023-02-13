<?php
declare(strict_types=1);

namespace acoby\models;

/**
 * Defines a list of possible history modes
 */
abstract class HistoryMode {
  const MODE_UNKNOWN = 0;
  const MODE_CREATED = 1;
  const MODE_CHANGED = 2;
  const MODE_DELETED = 3;
  
  /**
   * Creates a string representation of History.
   * 
   * @param int $mode
   * @return string
   */
  public static function toString(?int $mode) :string {
    switch ($mode) {
      case HistoryMode::MODE_CREATED: return "created";
      case HistoryMode::MODE_CHANGED: return "modified";
      case HistoryMode::MODE_DELETED: return "deleted";
      default: return "did unknown";
    }
  }
  
  /**
   * Checks of mode exists
   * 
   * @param int $mode
   * @return bool
   */
  public static function exists(?int $mode) :bool {
    switch ($mode) {
      case HistoryMode::MODE_CREATED:
      case HistoryMode::MODE_CHANGED:
      case HistoryMode::MODE_DELETED:
        return true;
      default: 
        return false;
    }
  }
}