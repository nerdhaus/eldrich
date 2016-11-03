<?php

namespace Drupal\ep_complex_values\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Parses a simple damage string into its component parts.
 *
 * @MigrateProcessPlugin(
 *   id = "damage_string",
 *   handle_multiples = FALSE
 * )
 *
 * @ingroup migration
 */
abstract class DamageParser extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Parse the format: SKILL NAME: FIELD NAME 99 (SPECIALIZATION STRING)
    $value = str_replace(' ', '', $value);
    $pattern = "((?<dice>[0-9]+)d10)?((?<mod_function>[+-/*])(?<modifier>[0-9]+))?";
    $matches = preg_match($pattern, $value);
    $results = array(
      'dice' => $matches['dice'],
      'mod_function' => $matches['mod_function'],
      'modifier' => $matches['modifier'],
    );
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return FALSE;
  }

}
