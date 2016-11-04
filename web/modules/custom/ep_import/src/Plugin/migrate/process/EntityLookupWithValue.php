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

    if ($entity = parent::transform($title, $migrateExecutable, $row, $destinationProperty)) {
      $results = array(
        'target_id' => $entity,
        $this->extraKey => $notes,
      );
    }
    return $results;
  }

  /**
   * Checks for the existence of some value.
   *
   * @param $value
   * The value to query.
   *
   * @return mixed|null
   *   Entity id if the queried entity exists. Otherwise NULL.
   */
  protected function query($value) {
    // Entity queries typically are case-insensitive. Therefore, we need to
    // handle case sensitive filtering as a post-query step. By default, it
    // filters case insensitive. Change to true if that is not the desired
    // outcome.
    $ignoreCase = !empty($this->configuration['ignore_case']) ?: FALSE;

    $multiple = is_array($value);

    $query = $this->entityManager->getStorage($this->lookupEntityType)
      ->getQuery()
      ->condition($this->lookupValueKey, $value, $multiple ? 'IN' : NULL);

    if ($this->lookupBundleKey) {
      // This is the one line we're changing. If multiple bundles are specified,
      // we can query them all. It's kind of dumb, but we need it so we can split
      // Generic Gear and Weapons across multiple node types while still pulling
      // them in with a single query.
      $query->condition($this->lookupBundleKey, $this->lookupBundle, is_array($this->lookupBundle) ? 'IN' : NULL);
    }
    $results = $query->execute();

    if (empty($results)) {
      return NULL;
    }

    // By default do a case-sensitive comparison.
    if (!$ignoreCase) {
      // Returns the entity's identifier.
      foreach ($results as $k => $identifier) {
        $result_value = $this->entityManager->getStorage($this->lookupEntityType)->load($identifier)->{$this->lookupValueKey}->value;
        if (($multiple && !in_array($result_value, $value, TRUE)) || (!$multiple && $result_value !== $value)) {
          unset($results[$k]);
        }
      }
    }

    if ($multiple && !empty($this->destinationProperty)) {
      array_walk($results, function (&$value) {
        $value = [$this->destinationProperty => $value];
      });
    }

    return $multiple ? array_values($results) : reset($results);
  }
}

