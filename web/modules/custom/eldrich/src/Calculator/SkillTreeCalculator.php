<?php

namespace Drupal\eldrich\Calculator;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Entity\FieldableEntityInterface;


/**
 * Class SkillTreeCalculator
 * @package Drupal\eldrich\Calculator
 *
 * Responsible for the thankless task of adding stat and gear bonuses to every
 * skill in a list.
 *
 * The goal is to take a given entity, walk its child fields, and produce:
 *
 * - A set dictionary of skills, keyed by skill_id:fieldname, containing:
 *   - Skill Entity
 *   - Field
 *   - Specialization
 *   - constant
 *     - baseline
 *     - points
 *     - bonus
 *     - total
 *   - conditional
 *     - baseline
 *     - points
 *     - bonus
 *     - total
 *
 * - entity->field_skills
 * - entity->field_traits->field_skills
 * - entity->field_sleights->field_skills
 * - entity->field_morph->field_skills
 * - entity->field_morph->field_skills
 * - entity->field_morph->field_traits->field_skills
 * - entity->field_morph->field_augmentations->field_skills
 * - entity->field_morph->field_augmentations->field_skills

 */
class SkillTreeCalculator extends EldrichBaseCalculator {
  public static function total(FieldableEntityInterface $entity, Array $stats) {

  }

  public static function walkTree(FieldableEntityInterface $entity, Array $stats) {

    foreach ($entity->field_traits as $tr) {
      $t = $tr->entity;
      $ts = $tr->field_stats->getValue();
      if ($tr->field_conditional->value == TRUE) {
        StatTreeCalculator::addSets($stats['conditional'], $ts);
      }
      else {
        StatTreeCalculator::addSets($stats['constant'], $ts);
      }
    }
    $stats['total'] = $stats['baseline'];
    StatTreeCalculator::addSets($stats['total'], $stats['constant']);
    return $stats;
  }

}
