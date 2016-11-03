<?php

namespace Drupal\integer_time\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Parses a simple damage string into its component parts.
 *
 * @MigrateProcessPlugin(
 *   id = "integer_time",
 *   handle_multiples = TRUE
 * )
 *
 * @ingroup migration
 */
abstract class DamageParser extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return $this->parseTime($value);
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return FALSE;
  }

  protected function parseTime($string) {
    $matches = array();
    $number = 0;
    preg_match("/([0-9]+)([dhms])/", $string, $matches);
    if (!empty($matches)) {
      $string = '';
      foreach ($matches as $match) {
        switch ($match[2]) {
          case 'd':
            $number += $match[1] * 86400;
            break;
          case 'h':
            $number += $match[1] * 3600;
            break;
          case 'm':
            $number += $match[1] * 60;
            break;
          case 's':
            $number += $match[1];
            break;
        }
      }
    }
    return $number;
  }
}
