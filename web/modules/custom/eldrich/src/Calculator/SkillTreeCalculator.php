<?php

namespace Drupal\eldrich\Calculator;

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
 *   - Skill Name
 *   - Skill Entity
 *   - Field
 *   - Specialization
 *   - Linked Aptitude
 *   - Skill type
 *   - No Defaulting
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
 * - entity->field_morph->field_traits->field_skills
 * - entity->field_morph->field_augmentations->field_skills
 * - entity->field_morph->field_augmentations->field_skills
 */
class SkillTreeCalculator {
  /** @var array $skillList */
  private static $skillList = [];

  public static function total(FieldableEntityInterface $entity, Array $stats) {
    $skills = [];

    // Pre-initialize defaultable skills.
    foreach (static::getSkillList() as $nid => $skill) {
      $key = strtolower($skill->label());
      $skills[$key] = static::initRow();
      static::populateRow($skills[$key], $skill, $stats);
    }

    static::walkTree($skills, $entity, $stats);

    // If we're working with anything other than a full-fledged PC, subtract baseline
    // and bonus from points. Total is points. Shhhhh. Don't cheapen this.
    if ($entity->bundle() != 'pc') {
      foreach ($skills as $key => $data) {
        if ($skills[$key]['constant']['points'] > $skills[$key]['constant']['baseline']) {
          $skills[$key]['constant']['points'] = $skills[$key]['constant']['points'] - $skills[$key]['constant']['baseline'] + $skills[$key]['constant']['bonus'];
        }
        if ($skills[$key]['conditional']['points'] > $skills[$key]['conditional']['baseline']) {
          $skills[$key]['conditional']['points'] = $skills[$key]['conditional']['points'] - $skills[$key]['conditional']['baseline'] + $skills[$key]['conditional']['bonus'];
        }
      }
    }

    // Calculate the totals now!
    foreach ($skills as $key => $data) {
      $skills[$key]['constant']['total'] = $skills[$key]['constant']['baseline'] + $skills[$key]['constant']['points'] + $skills[$key]['constant']['bonus'];
      $skills[$key]['conditional']['total'] = $skills[$key]['conditional']['baseline'] + $skills[$key]['conditional']['points'] + $skills[$key]['conditional']['bonus'];
    }

    return $skills;
  }

  public static function walkTree(Array &$skills, FieldableEntityInterface $entity, Array $stats, $bonus = FALSE) {
    if (!empty($entity->field_skills)) {
      foreach ($entity->field_skills as $instance) {
        $key = strtolower($instance->entity->label());
        if ($instance->entity->field_is_field->value) {
          $key .= ':' . strtolower($instance->field);
        }

        if (!isset($skills[$key])) {
          $skills[$key] = static::initRow();
          static::populateRow($skills[$key], $instance->entity, $stats);
        }

        $skills[$key]['field'] = $instance->field;
        $skills[$key]['specialization'] = $instance->specialization;

        if (isset($entity->field_conditional) && $entity->field_conditional->value) {
          $skills[$key]['conditional'][$bonus ? 'bonus' : 'points'] += $instance->points;
        }
        else {
          $skills[$key]['conditional'][$bonus ? 'bonus' : 'points'] += $instance->points;
          $skills[$key]['constant'][$bonus ? 'bonus' : 'points'] += $instance->points;
        }
      }
    }

    if (!empty($entity->field_traits)) {
      foreach ($entity->field_traits as $trait) {
        static::walkTree($skills, $trait->entity, $stats, TRUE);
      }
    }

    if (!empty($entity->field_sleights)) {
      foreach ($entity->field_sleights as $sleight) {
        static::walkTree($skills, $sleight->entity, $stats, TRUE);
      }
    }

    if (!empty($entity->field_augmentations)) {
      foreach ($entity->field_augmentations as $aug) {
        static::walkTree($skills, $aug->entity, $stats, TRUE);
      }
    }

    if (!empty($entity->field_morph) && !$entity->field_morph->isEmpty()) {
      $morph = $entity->field_morph->entity;
      static::walkTree($skills, $morph, $stats, TRUE);
    }
  }

  private static function populateRow(Array &$row, FieldableEntityInterface $skill, Array $stats) {
    $row['name'] = $skill->label();
    $row['entity'] = $skill;
    $row['aptitude'] = strtolower($skill->field_linked_aptitude->entity->field_code->value);
    $row['type'] = $skill->field_skill_type->entity->label();
    $row['no_defaulting'] = $skill->field_no_defaulting->value;

    $row['constant']['baseline'] = $stats['mind']['baseline'][$row['aptitude']];
    $row['conditional']['baseline'] = $stats['mind']['baseline'][$row['aptitude']];
  }

  private static function initRow() {
    return [
      'name' => '',
      'entity' => NULL,
      'field' => '',
      'specialization' => '',
      'type' => '',
      'aptitude' => '',
      'no_defaulting' => TRUE,
      'constant' => [
        'baseline' => 0,
        'points' => 0,
        'bonus' => 0,
        'total' => 0,
      ],
      'conditional' => [
        'baseline' => 0,
        'points' => 0,
        'bonus' => 0,
        'total' => 0,
      ],
    ];
  }

  public static function getSkillList() {
    // Because field skills never exist in a vacuum, we'll exclude them from
    // this list and rely on concrete player skills to populate them.
    // We'll filter out non-defaultable skills for the same reason.
    if (empty(static::$skillList)) {

      $nids = \Drupal::entityQuery('node')
        ->condition('type', 'skill')
        ->condition('field_is_field', TRUE, '<>')
        ->condition('field_no_defaulting', TRUE, '<>')
        ->execute();

      $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

      foreach ($nodes as $skill) {
        static::$skillList[$skill->id()] = $skill;
      }
    }
    return static::$skillList;
  }
}
