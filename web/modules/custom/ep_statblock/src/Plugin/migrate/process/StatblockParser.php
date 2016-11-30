<?php

namespace Drupal\ep_statblock\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Parses a simple damage string into its component parts.
 *
 * @MigrateProcessPlugin(
 *   id = "stat_block",
 *   handle_multiples = FALSE
 * )
 *
 * @ingroup migration
 */
class StatBlockParser extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $raw = explode(',', $value);
    $stats = ['cog', 'coo', 'int', 'ref', 'sav', 'som', 'wil', 'mox', 'spd', 'dur'];
    $results = [];
    for ($i = 0; $i < 10; $i++) {
      if (isset($raw[$i]) && $raw[$i] !== 0) {
        $results[$stats[$i]] = $raw[$i];
      }
    }
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return FALSE;
  }
}
