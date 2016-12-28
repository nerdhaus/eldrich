<?php

namespace Drupal\eldrich\Calculator;
use Drupal\Core\Field\FieldItemListInterface;
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
 *   - constant (always applies)
 *   - conditional (conditionally applies)
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
class StatTreeCalculator extends EldrichBaseCalculator {
  public static function total(FieldableEntityInterface $entity) {
    $statgroups = ['total' => [], 'mind' => [], 'shell' => []];

    // Do the tree walk
    switch ($entity->bundle()) {
      case 'pc':
        // PCs store ego stats but reference morph stats
        $statgroups['total'] = $statgroups['mind'] = StatTreeCalculator::walkTree($entity);
        if (!$entity->field_morph->isEmpty()) {
          $statgroups['shell'] = StatTreeCalculator::walkTree($entity->field_morph->entity);

          foreach (['baseline', 'constant', 'conditional'] as $group) {
            StatTreeCalculator::addSets($statgroups['total'][$group], $statgroups['shell'][$group]);
          }
        }
        StatTreeCalculator::combineTotals($statgroups);
        break;

      case 'npc':
        // NPCs store ego and morph stats pre-summed. We still want to get
        // the conditionals for them, though.
        $statgroups['total'] = $statgroups['mind'] = StatTreeCalculator::walkTree($entity);

        // We fudge some stuff to constant bonuses never get rolled in.
        $statgroups['total']['constant'] = $statgroups['total']['baseline'];

        if (!$entity->field_morph->isEmpty()) {
          $statgroups['shell'] = StatTreeCalculator::walkTree($entity->field_morph->entity);
          StatTreeCalculator::addSets($statgroups['total']['conditional'], $statgroups['shell']['conditional']);
        }

        unset($statgroups['total']['baseline']);
        StatTreeCalculator::calculateProperties($statgroups['total']['constant']);
        StatTreeCalculator::calculateProperties($statgroups['total']['conditional']);
        break;

      case 'robot':
        // Robots are the opposite — they store shell and reference mind stats
        $statgroups['total'] = $statgroups['shell'] = StatTreeCalculator::walkTree($entity);

        if (!$entity->field_default_ai->isEmpty()) {
          $statgroups['mind'] = StatTreeCalculator::walkTree($entity->field_default_ai->entity);
          // Copy the ego stat to 'combined' and sum the morph stats in.
          foreach (['baseline', 'constant', 'conditional'] as $group) {
            StatTreeCalculator::addSets($statgroups['total'][$group], $statgroups['mind'][$group]);
          }
        }

        StatTreeCalculator::combineTotal($statgroups['total']);
        break;

      case 'muse':
      case 'creature':
      case 'mind':
        // These are really simple — they roll everything up beforehand.
        $statgroups['total'] = StatTreeCalculator::walkTree($entity);
        StatTreeCalculator::combineTotal($statgroups['total']);
        break;
    }

    return $statgroups;
  }

  public static function walkTree(FieldableEntityInterface $entity) {
    $stats = ['baseline' => [], 'constant' => [], 'conditional' => []];
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
            if ($e->field_conditional->value == TRUE) {
              StatTreeCalculator::addSets($stats['conditional'], $es);
            }
            else {
              StatTreeCalculator::addSets($stats['constant'], $es);
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

  public static function enforceMaxStats(Array &$a, Array $max) {
    foreach ($a as $key => $value) {
      if (isset($max[$key])) {
        $a[$key] = min($a[$key], $max[$key]);
      }
    }
  }

  public static function combineTotal(Array &$statgroups) {
    // Add the baseline values to the constant and conditional sets
    StatTreeCalculator::addSets($statgroups['constant'], $statgroups['baseline']);
    StatTreeCalculator::addSets($statgroups['conditional'], $statgroups['baseline']);

    // Calculate derived properties.
    unset($statgroups['baseline']);
    StatTreeCalculator::calculateProperties($statgroups['constant']);
    StatTreeCalculator::calculateProperties($statgroups['conditional']);
  }
}
