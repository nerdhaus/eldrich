<?php

namespace Drupal\saved_query\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Saved query entities.
 */
class SavedQueryViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
