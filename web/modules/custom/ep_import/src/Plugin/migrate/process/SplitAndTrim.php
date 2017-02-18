<?php

namespace Drupal\ep_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * This plugin takes a delimited string.
 *
 * @MigrateProcessPlugin(
 *   id = "split_and_trim"
 * )
 */
class SplitAndTrim extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $delimiter = $this->configuration['delimiter'] ?: ',';
    $tuck = $this->configuration['tuck'];
    $values = explode($delimiter, $value);

    for($i = 0; $i < count($values); $i++) {
      $values[$i] = trim($values[$i]);
    }

    if (!empty($tuck)) {
      foreach ($values as $key => $value) {
        $values[$key] = [$tuck => $value];
      }
    }

    return $values;
  }
}
