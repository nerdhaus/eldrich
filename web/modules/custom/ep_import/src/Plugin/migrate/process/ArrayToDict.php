<?php

namespace Drupal\ep_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * This plugin takes a delimited string.
 *
 * @MigrateProcessPlugin(
 *   id = "array_to_dict"
 * )
 */
class ArrayToDict extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $conf = $this->configuration;
    $keys = $conf['keys'];

    if (!is_array($value)) {
      $delimiter = empty($conf['delimiter']) ? ',' : $conf['delimiter'];
      $values = explode($delimiter, $value, count($keys));
    }
    else {
      $values = $value;
    }

    for($i = 0; $i < min(count($keys), count($values)); $i++) {
      $results[$keys[$i]] = trim($values[$i]);
    }

    return $results;
  }
}
