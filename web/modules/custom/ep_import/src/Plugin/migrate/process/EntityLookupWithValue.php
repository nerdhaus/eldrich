<?php

namespace Drupal\ep_import\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_plus\Plugin\migrate\process\EntityLookup;

/**
 * This plugin splits out skill references into their component.
 *
 * @MigrateProcessPlugin(
 *   id = "entity_lookup_plus",
 *   handle_multiples = FALSE
 * )
 */
class EntityLookupWithValue extends EntityLookup {
  /** @var string */
  protected $extraKey;

  /** @var string */
  protected $delimiter;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrateExecutable, Row $row, $destinationProperty) {
    // We want to handle these nicely later.
    $this->extraKey = 'override';
    $this->delimiter = ':';
    $results = [];

    if (strpos($value, $this->delimiter)) {
      $values = explode($this->delimiter, $value);
      $title = trim($values[0]);
      $notes = trim($values[1]);
    }
    else {
      $title = trim($value);
      $notes = NULL;
    }
drush_print_r($title);
    if ($entity = parent::transform($title, $migrateExecutable, $row, $destinationProperty)) {
      $results = array(
        'target_id' => $entity,
        $this->extraKey => $notes,
      );
      drush_print_r($results);
    }
    return $results;
  }
}

