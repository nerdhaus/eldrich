<?php

namespace Drupal\operation\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Parses a number and operator string into discrete values.
 *
 * @MigrateProcessPlugin(
 *   id = "operation_string",
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
    return operation_parse_string($value);
  }
}
