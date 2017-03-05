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
 *   - Skill Entity URL
 *   - Field
 *   - Specialization
 *   - Linked Aptitude
 *   - Skill type
 *   - No Defaulting
 *   - constant
 *     - default
 *     - points
 *     - baseline
 *     - bonus
 *     - total
 *   - conditional
 *     - default
 *     - points
 *     - baseline
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

    if (!empty($stats) && in_array($entity->bundle(), ['pc', 'npc', 'creature', 'robot', 'mind'])) {
      // Pre-initialize defaultable skills.
      foreach (static::getSkillList() as $nid => $skill) {
        $key = strtolower($skill->label());
        $skills[$key] = static::initRow();
        static::populateRow($skills[$key], $skill, $stats);
      }

      static::walkTree($skills, $entity, $stats);
    }

    static::handleGearBonuses($skills, $entity, $stats);

    // If we're working with anything other than a full-fledged PC, subtract default
    // and bonus from points. Total is points. Shhhhh. Don't cheapen this.
    if (!in_array($entity->bundle(), ['pc', 'npc'])) {
      foreach ($skills as $key => $data) {
        if ($skills[$key]['constant']['points'] > $skills[$key]['constant']['default']) {
          $skills[$key]['constant']['points'] = $skills[$key]['constant']['points'] - $skills[$key]['constant']['default'] + $skills[$key]['constant']['bonus'];
        }
        if ($skills[$key]['conditional']['points'] > $skills[$key]['conditional']['default']) {
          $skills[$key]['conditional']['points'] = $skills[$key]['conditional']['points'] - $skills[$key]['conditional']['default'] + $skills[$key]['conditional']['bonus'];
        }
      }
    }

    // Calculate the totals now!
    foreach ($skills as $key => $data) {
      $skills[$key]['constant']['baseline'] = static::accountForPointCost($skills[$key]['constant']['default'] + $skills[$key]['constant']['points']);
      $skills[$key]['conditional']['baseline'] = static::accountForPointCost($skills[$key]['conditional']['default'] + $skills[$key]['conditional']['points']);

      $skills[$key]['constant']['total'] = $skills[$key]['constant']['baseline'] + $skills[$key]['constant']['bonus'];
      $skills[$key]['conditional']['total'] = $skills[$key]['conditional']['baseline'] + $skills[$key]['conditional']['bonus'];
    }

    ksort($skills);

    return $skills;
  }

  public static function walkTree(Array &$skills, FieldableEntityInterface $entity, Array $stats, $bonus = FALSE) {
    if (!empty($entity->field_skills)) {
      foreach ($entity->field_skills as $instance) {
        $key = strtolower($instance->entity->label());

        // TODO: We shouldn't treat ALL exotic weapons the same, but for now it's easier.
        if ($instance->entity->field_is_field->value && !strpos($instance->entity->label(), 'xotic')) {
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

    if (!empty($entity->field_default_ai) && !$entity->field_default_ai->isEmpty()) {
      $ai = $entity->field_default_ai->entity;
      static::walkTree($skills, $ai, $stats, TRUE);
    }
  }

  private static function populateRow(Array &$row, FieldableEntityInterface $skill, Array $stats) {
    $row['name'] = $skill->label();
    $row['entity'] = $skill;
    $row['url'] = $skill->toUrl();
    $row['aptitude'] = strtolower($skill->field_linked_aptitude->entity->field_code->value);
    $row['type'] = $skill->field_skill_type->entity->label();
    $row['no_defaulting'] = $skill->field_no_defaulting->value;

    // The 'default' for skills is the 'constant total' for minds.
    if (!empty($stats['mind'])) {
      $row['constant']['default'] = $stats['mind']['constant'][$row['aptitude']];
      $row['conditional']['default'] = $stats['mind']['conditional'][$row['aptitude']];
    }

    if (!empty($stats['shell'])) {
      $row['constant']['bonus'] = $stats['shell']['constant'][$row['aptitude']];
      $row['conditional']['bonus'] = $stats['shell']['conditional'][$row['aptitude']];
    }
  }


  // Increasing skills above 80 (without morph bonuses) costs double points.
  // This handles it in the simplest way possible.
  private static function accountForPointCost($skill) {
    if ($skill > 60) {
      $remainder = floor(($skill - 60) / 2);
      $skill = 60 + $remainder;
    }
    return $skill;
  }

  // Until we actually track what gear is in use vs. owned, we won't actually
  // roll owned gear into this. We WILL account for worn armor, though, mostly
  // to handle the special case of Smart Clothing and Chameleon Cloak bonuses
  // to skills.
  private static function handleGearBonuses(Array &$skills, FieldableEntityInterface $entity, Array $stats) {
    if ($entity->hasField('equipped_armor')) {
      foreach ($entity->equipped_armor as $f) {
        $e = $f->entity;
        foreach ($e->field_armor as $a) {
          static::walkTree($skills, $a->entity, $stats);
        }
        foreach ($e->field_armor_mods as $m) {
          static::walkTree($skills, $m->entity, $stats);
        }
      }
    }
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
        'default' => 0,
        'points' => 0,
        'baseline' => 0,
        'bonus' => 0,
        'total' => 0,
      ],
      'conditional' => [
        'default' => 0,
        'points' => 0,
        'baseline' => 0,
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
