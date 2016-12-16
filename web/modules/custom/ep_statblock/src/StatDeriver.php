<?php

namespace Drupal\ep_statblock;

use Drupal\Core\TypedData\TypedData;

/**
 * A computed property for Eclipse Phase stats.
 *
 * Required settings (below the definition's 'settings' key) are:
 *  - stat: The stat to derive from existing data.
 */
class StatDeriver extends TypedData {
  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::getValue().
   */
  public function getValue($langcode = NULL) {
    $item = $this->getParent();

    switch ($this->definition->getSetting('property')) {
      case 'init':
        if (!is_null($item->int) && !is_null($item->ref)) {
          return ceil(($item->int + $item->ref) / 5);
        }
        break;
      case 'luc':
        if (!is_null($item->wil)) {
          return $item->wil * 2;
        }
        break;
      case 'tt':
        if (!is_null($item->wil)) {
          return ceil($item->wil * 2 / 5);
        }
        break;
      case 'ir':
        if (!is_null($item->wil)) {
          return $item->wil * 4;
        }
        break;
      case 'wt':
        if (!is_null($item->dur)) {
          return ceil($item->dur / 5);
        }
        break;
      case 'dr':
        if (!is_null($item->dur)) {
          return round($item->dur * ($item->synthetic ? 2 : 1.5));
        }
        break;
      case 'db':
        if (!is_null($item->som)) {
          return floor($item->som / 10);
        }
        break;
    }

    return null;
  }
}
