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

    if (strpos($value, $this->delimiter)) {
      $values = explode($this->delimiter, $value);
      $results = array(
        'target_id' => parent::transform(trim($values[0]), $migrateExecutable, $row, $destinationProperty),
        $this->extraKey => trim($values[1]),
      );
      return $results;
    }
    else {
      return parent::transform(trim($value), $migrateExecutable, $row, $destinationProperty);
    }
  }
}
