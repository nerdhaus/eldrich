<?php

namespace Drupal\integer_time\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Parses a time string (10d 2h) into its component parts.
 *
 * @MigrateProcessPlugin(
 *   id = "integer_time",
 *   handle_multiples = FALSE
 * )
 *
 * @ingroup migration
 */
class IntegerTimeParser extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $value = str_replace('-', '–', $value); // Pedantic
    $value = str_replace(' ', '', $value); // Scrubby
    $multiple = strpos($value, '–'); // Should we go deeper?

    if ($multiple) {
      $segments = explode('–', $value);
      return [
        'from' => $this->parseTime($segments[0]),
        'to' => $this->parseTime($segments[1]),
      ];
    }
    else {
      return [
        'from' => $this->parseTime($value),
        'to' => $this->parseTime($value),
      ];
    }
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
    preg_match_all("/([0-9]+)([wdhms])/", $string, $matches);

    for ($i = 0; $i< count($matches); $i++) {
      switch ($matches[2][$i]) {
        case 'w':
          $number += (int)($matches[1][$i]) * 604800;
          break;
        case 'd':
          $number += (int)($matches[1][$i]) * 86400;
          break;
        case 'h':
          $number += (int)($matches[1][$i]) * 3600;
          break;
        case 'm':
          $number += (int)($matches[1][$i]) * 60;
          break;
        case 's':
          $number += (int)($matches[1][$i]);
          break;
      }
    }

    return $number;
  }
}
