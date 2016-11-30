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
    // Parse the format: SKILL NAME: FIELD NAME 99 (SPECIALIZATION STRING)
    $pattern = "/(?<skill>[A-Za-z ]+)(\:\s+?(?<field>[A-Za-z ]+))?\s+?(?<points>[0-9]+)?\s*(\((?<specialization>([a-zA-Z ]+))\))?/";
    $results = [];
    $matches = [];
    preg_match($pattern, $value, $matches);

    if (!empty($matches['skill'])
      && !empty($matches['points'])
      && $entity = parent::transform(trim($matches['skill']), $migrateExecutable, $row, $destinationProperty)) {

      $results = array(
        'raw_skill' => $matches['skill'],
        'target_id' => $entity,
        'field' => trim($matches['field']),
        'specialization' => trim($matches['specialization']),
        'points' => trim($matches['points']),
      );
    }
    return $results;
  }
}
