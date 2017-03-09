<?php

namespace Drupal\ep_skill_reference\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_plus\Plugin\migrate\process\EntityLookup;

/**
 * This plugin splits out skill references into their component.
 *
 * @MigrateProcessPlugin(
 *   id = "skill_lookup",
 *   handle_multiples = FALSE
 * )
 */
class SkillReferenceParser extends EntityLookup {
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrateExecutable, Row $row, $destinationProperty) {
    return ep_skill_reference_parse($value);
  }
}
