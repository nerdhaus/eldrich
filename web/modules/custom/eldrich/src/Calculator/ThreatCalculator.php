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
 * - Total Armor (K + E)
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

  public static function total(Array $stats, Array $skills, Array $armor, Array $weapons) {
    $data = static::getDefaults();

    // Defense data
    $data['raw']['dur'] = $stats['total']['conditional']['dur'];
    $data['raw']['spd'] = $stats['total']['conditional']['spd'];
    $data['raw']['armor'] = $armor['energy'] + $armor['kinetic'];
    $data['raw']['fray'] = $skills['fray']['conditional']['total'];
    $data['results']['defense'] = ($data['raw']['dur'] / 5) * ($data['raw']['fray'] / 100) + $data['raw']['armor'];

    // Offense data
    // $data['results']['offense'] = ($data['raw']['dv'] * $data['raw']['skill'] / 100) + $data['raw']['ap'];

    // Total everything up
    // $data['results']['threat'] = $data['results']['defense'] + ($data['results']['offense'] * $data['raw']['spd']);
    // $data['rating'] = floor($data['results']['threat']);

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