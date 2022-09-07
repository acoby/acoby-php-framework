<?php
declare(strict_types=1);

namespace acoby\services;

/**
 *
 * @author thoralf
 */
interface HistoryService {
  const MODE_UNKNOWN = 0;
  const MODE_ADD = 1;
  const MODE_CHANGED = 2;
  const MODE_DELETED = 3;
  
}