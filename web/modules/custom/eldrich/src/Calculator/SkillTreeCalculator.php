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
 * - A set dictionary of skills, keyed by skillname:fieldname, containing:
 *   - Skill Entity
 *   - Field
 *   - Specialization
 *   - Constant skill points (including stats if passed into constructor)
 *   - Conditional skill bonus points (including conditional stats if passed in)
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
  public static function total(FieldableEntityInterface $entity) {

  }
}