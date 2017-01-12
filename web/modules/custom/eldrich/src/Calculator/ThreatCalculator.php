<?php

namespace Drupal\eldrich\Calculator;

use Drupal\Core\Entity\EntityInterface;


/**
 * Class SkillTreeCalculator
 * @package Drupal\eldrich\Calculator
 *
 * Responsible for the thankless task of adding stat and gear bonuses to every
 * skill in a list.
 *
 * The goal is to take a given entity, walk its accompanying data, and produce:
 *
 * - Durability
 * - Fray Skill
 * - Total Armor (K + E) / 2
 * - DV per turn w/best weapon
 *   (x2 for SA, x2 +1d10 for BF, +3d10 for FA)
 * - AP with best weapon
 * - Attack skill with best weapon
 * - Speed (with conditional bonuses)
 *
 * From that, we calculate:
 *
 * - Defense value: ((DUR / 5) * (Fray / 100)) + Armor
 * - Offense value: (DV * Skill / 100) + AP
 * - Threat value: Defense + (Offense * SPD)
 * - Threat rating: floor(Threat / 5)
 */

class ThreatCalculator {

  public static function total(Array $stats, Array $skills, Array $armor, Array $weapons, Array $sleights = []) {
    $data = static::getDefaults();

    if (empty($stats) || empty($skills) || empty($weapons)) {
      return $data;
    }

    // Defense data
    $data['raw']['dur'] = $stats['total']['conditional']['dur'];
    $data['raw']['spd'] = $stats['total']['conditional']['spd'];
    if (!empty($armor)) {
      $data['raw']['armor'] = floor(($armor['energy'] + $armor['kinetic']) / 2);
    }
    $data['raw']['fray'] = $skills['fray']['conditional']['total'];
    $data['results']['defense'] = ($data['raw']['dur'] / 5) * ($data['raw']['fray'] / 100) + $data['raw']['armor'];

    // We want to pick the best/strongest weapon, so we'll just brute force it.
    $tmp_weapons = [];
    foreach ($weapons as $weapon) {
      $tmp_weapon = [
        'dice' => $weapon['damage']['dice'],
        'mod' => $weapon['damage']['mod'],
        'mod_operation' => $weapon['damage']['mod_operation'],
        'multiplier' => $weapon['damage']['multiplier'],
        'ap' => $weapon['damage']['ap']
      ];
      if ($weapon['linked_skill']) {
        $tmp_weapon['skill'] = min($skills[$weapon['linked_skill']]['conditional']['total'] + $weapon['skill_bonus'], 100);
      }
      else {
        $tmp_weapon['skill'] = 0;
      }

      // Prefer full auto, as it's the best representative of max damage
      // without having to change clips.
      if (in_array('FA', array_keys($weapon['modes']))) {
        $tmp_weapon['dice'] += 3;
      }
      elseif (in_array('BF', array_keys($weapon['modes']))) {
        $tmp_weapon['dice'] += 1;
        $tmp_weapon['multiplier'] = $tmp_weapon['multiplier'] * 2;
      }
      elseif (in_array('SA', array_keys($weapon['modes']))) {
        $tmp_weapon['multiplier'] = $tmp_weapon['multiplier'] * 2;
      }

      if ($tmp_weapon['mod'] < 0) {
        $tmp_weapon['mod_operation'] = '-';
        $tmp_weapon['mod'] = abs($data['damage']['mod']);
      }

      $avg = $tmp_weapon['dice'] * 5;
      $avg = operation_calculate_result($avg, $tmp_weapon['mod_operation'], $tmp_weapon['mod']);
      $tmp_weapon['average'] = intval(round($avg * $tmp_weapon['multiplier']));

      $tmp_weapons[$tmp_weapon['average']] = $tmp_weapon;
    }

    if (!empty($tmp_weapons)) {
      ksort($tmp_weapons);
      $chosen_weapon = array_pop($tmp_weapons);
      $data['raw']['ap'] = $chosen_weapon['ap'];
      $data['raw']['dv'] = $chosen_weapon['average'];
      $data['raw']['skill'] = $chosen_weapon['skill'];
    }


    $data['results']['offense'] = ($data['raw']['dv'] * $data['raw']['skill'] / 100) - $data['raw']['ap'];

    // Total everything up
    $data['results']['threat'] = $data['results']['defense'] + ($data['results']['offense'] * $data['raw']['spd']);
    $data['rating'] = floor($data['results']['threat'] / 5);

    return $data;
  }

  private static function getDefaults() {
    $data = [
      'raw' => [
        'dur' => 0,
        'fray' => 0,
        'armor' => 0,
        'dv' => 0,
        'ap' => 0,
        'skill' => 0,
        'spd' => 1,
      ],
      'results' => [
        'defense' => 0,
        'offense' => 0,
        'threat' => 0,
      ],
      'rating' => 0,
    ];
    return $data;
  }
}
