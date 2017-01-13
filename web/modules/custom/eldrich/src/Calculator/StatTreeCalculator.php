<?php

namespace Drupal\eldrich\Calculator;

use Drupal\Core\Entity\FieldableEntityInterface;


/**
 * Class StatTreeCalculator
 * @package Drupal\eldrich\Calculator
 *
 * Responsible for the thankless task of calculating aggregate stats.
 *
 * The goal is to take a given entity, walk its child fields, and produce:
 *
 * - mind
 *   - baseline (raw stat values, no derivatives or bonuses)
 *   - constant (bonuses that always apply)
 *   - conditional (bonuses that don't)
 * - shell
 *   - baseline (raw stat values, no derivatives or bonuses)
 *   - constant (bonuses that always apply)
 *   - conditional (bonuses that don't)
 * - total
 *   - baseline (Not used, but still exists for simplicity)
 *   - constant (always applies)
 *   - conditional (conditionally applies)
 * - entities (pre-built set of data about each stat)
 *
 * To derive this information, it's necessary to walk through:
 *
 * - entity->field_stats
 * - entity->field_traits
 * - entity->field_sleights
 * - entity->field_morph->field_stats
 * - entity->field_morph->field_traits
 * - entity->field_morph->field_traits->field_stats
 * - entity->field_morph->field_augmentations->field_stats
 * - entity->field_morph->field_augmentations->field_stats
 *
 */
class StatTreeCalculator {

  /** @var array $statList */
  private static $statList = [];

  public static function total(FieldableEntityInterface $entity) {
    $statgroups = ['total' => [], 'mind' => [], 'shell' => []];

    // Do the tree walk
    switch ($entity->bundle()) {
      case 'npc':
      case 'pc':
      // PCs store ego stats but reference morph stats
      $statgroups['total'] = $statgroups['mind'] = static::walkTree($entity);
      if (!$entity->field_morph->isEmpty()) {
        $cap = static::getStatCap($entity->field_morph->entity);
        $statgroups['shell'] = static::walkTree($entity->field_morph->entity);

        foreach (['baseline', 'constant', 'conditional'] as $group) {
          static::addSets($statgroups['total'][$group], $statgroups['shell'][$group]);
        }
      }
      else {
        $cap = static::getStatCap();
      }

      if (empty($statgroups['mind']) || empty($statgroups['shell']) || empty($statgroups['total'])) {
        break;
      }

      static::combineTotal($statgroups['mind'], $cap);
      static::combineTotal($statgroups['shell'], $cap);
      static::combineTotal($statgroups['total'], $cap);
      break;

      case 'robot':
        // Robots are the opposite — they store shell and reference mind stats
        $statgroups['total'] = $statgroups['shell'] = static::walkTree($entity);

        if (!$entity->field_default_ai->isEmpty()) {
          $statgroups['mind'] = static::walkTree($entity->field_default_ai->entity);
          // Copy the ego stat to 'combined' and sum the morph stats in.
          foreach (['baseline', 'constant', 'conditional'] as $group) {
            static::addSets($statgroups['total'][$group], $statgroups['mind'][$group]);
          }
        }

        static::combineTotal($statgroups['total']);
        break;

      case 'muse':
      case 'creature':
      case 'mind':
        // These are really simple — they roll everything up beforehand.
        $statgroups['total'] = static::walkTree($entity);
        static::combineTotal($statgroups['total']);
        break;
    }

    return $statgroups;
  }

  public static function walkTree(FieldableEntityInterface $entity) {
    $stats = ['baseline' => static::initSet(), 'constant' => static::initSet(), 'conditional' => static::initSet()];
    $stats['baseline'] = $entity->field_stats->getValue();

    // Honestly this irritates me.
    if (isset($stats['baseline'][0])) {
      $stats['baseline'] = $stats['baseline'][0];
    }

    foreach (['field_traits', 'field_augmentations', 'field_sleights'] as $field) {
      if (!empty($entity->{$field})) {
        foreach ($entity->{$field} as $f) {
          $e = $f->entity;
          if ($es = $e->field_stats->getValue()) {
            // Suddenly, deltas!
            if (count($es) == 1) {
              $es = reset($es);
            }
            if ($e->field_conditional->value == TRUE) {
              static::addSets($stats['conditional'], $es);
            }
            else {
              static::addSets($stats['constant'], $es);
            }
          }
        }
      }
    }
    return $stats;
  }

  private static function addSets(Array &$a, $b = []) {
    foreach ($b as $key => $value) {
      if ($key != '_attributes') {
        if (isset($a[$key])) {
          $a[$key] += $value;
        }
        else {
          $a[$key] = $value;
        }
      }
    }
  }

  public static function calculateProperties(Array &$a) {
    // We add rather than replace these values, because
    // existing bonuses might already be in place there.

    if (isset($a['int']) && isset($a['ref'])) {
      $a['init'] = isset($a['init']) ? $a['init'] : NULL;
      $a['init'] += ceil(($a['int'] + $a['ref']) / 5);
    }

    if (isset($a['wil'])) {
      $a['luc'] = isset($a['luc']) ? $a['luc'] : NULL;
      $a['luc'] += $a['wil'] * 2;
    }

    if (isset($a['luc'])) {
      $a['tt'] = isset($a['tt']) ? $a['tt'] : NULL;
      $a['tt'] += ceil($a['luc'] / 5);
    }

    if (isset($a['wil'])) {
      $a['ir'] = isset($a['ir']) ? $a['ir'] : NULL;
      $a['ir'] += $a['wil'] * 4;
    }

    if (isset($a['dur'])) {
      $a['wt'] = isset($a['wt']) ? $a['wt'] : NULL;
      $a['wt'] += ceil($a['dur'] / 5);
    }

    if (isset($a['som'])) {
      $a['db'] = isset($a['db']) ? $a['db'] : NULL;
      $a['db'] += floor($a['som'] / 10);
    }

    if (isset($a['dur'])) {
      $a['dr'] = isset($a['dr']) ? $a['dr'] : NULL;
      $a['dr'] += round($a['dur'] * (empty($a['synthetic']) ? 1.5 : 2 ));
    }
  }

  public static function combineTotal(Array &$statgroups, Array $cap = null) {
    // Add the baseline values to the constant and conditional sets
    static::addSets($statgroups['conditional'], $statgroups['constant']);
    static::addSets($statgroups['constant'], $statgroups['baseline']);
    static::addSets($statgroups['conditional'], $statgroups['baseline']);

    static::enforceStatCap($statgroups, $cap);

    // Calculate derived properties.
    static::calculateProperties($statgroups['constant']);
    static::calculateProperties($statgroups['conditional']);
  }

  private static function getStatList() {
    if (empty(static::$statList)) {
      $nids = \Drupal::entityQuery('node')
        ->condition('type', 'stat')
        ->execute();

      $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

      foreach ($nodes as $stat) {
        static::$statList[strtolower($stat->field_code->value)] = $stat;
      }
    }
    return static::$statList;
  }

  private static function getStatCap(FieldableEntityInterface $morph_instance = null) {
    if (!isset($morph_instance) || $morph_instance->field_model->isEmpty()) {
      $cap = [
        'cog' => 30,
        'coo' => 30,
        'int' => 30,
        'ref' => 30,
        'sav' => 30,
        'som' => 30,
        'wil' => 30,
      ];
    }
    else {
      $cap = $morph_instance->field_model->entity->field_stat_cap->getValue();
    }

    return $cap;
  }

  private static function enforceStatCap(Array &$statgroups, Array $cap = null) {
    if (!isset($cap)) {
      return;
    }

    // Speed and moxie can literally never go above this. Srsly.
    $cap['mox'] = 7;
    $cap['spd'] = 4;

    foreach ($statgroups as $group_key => $group) {
      foreach ($cap as $key => $value) {
        if (key_exists($key, $statgroups[$group_key])) {
          $statgroups[$group_key][$key] = min($statgroups[$group_key][$key], $value);
        }
      }
    }
  }

  private static function initSet() {
    return [
      'cog' => 0,
      'coo' => 0,
      'int' => 0,
      'ref' => 0,
      'sav' => 0,
      'som' => 0,
      'wil' => 0,
      'mox' => 0,
      'spd' => 0,
      'dur' => 0,
    ];
  }
}
