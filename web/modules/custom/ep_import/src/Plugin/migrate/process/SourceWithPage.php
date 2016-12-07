<?php

namespace Drupal\ep_import\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_plus\Plugin\migrate\process\EntityLookup;

/**
 * This plugin splits out skill references into their component.
 *
 * @MigrateProcessPlugin(
 *   id = "source_with_page",
 *   handle_multiples = FALSE
 * )
 */
class SourceWithPage extends EntityLookup {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrateExecutable, Row $row, $destinationProperty) {
    $results = [];
    if (strpos($value, '.')) {
      $values = explode('.', $value);
      $title = trim($values[0]);
      $page = trim($values[1]);
    }
    else {
      $title = trim($value);
      $page = NULL;
    }

    if ($entity = parent::transform($title, $migrateExecutable, $row, $destinationProperty)) {
      $results = array(
        'target_id' => $entity,
        'quantity' => $page,
      );
    }
    return $results;
  }
}

