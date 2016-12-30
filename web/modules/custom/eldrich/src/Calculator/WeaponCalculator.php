<?php

namespace Drupal\eldrich\Calculator;

use Drupal\Core\Entity\EntityInterface;


/**
 * Class WeaponCalculator
 * @package Drupal\eldrich\Calculator
 *
 * Responsible for collapsing weapon clusters into single stats.
 *
 * The goal is to take a weapon_instance eck entity and produce
 * a structured array with the following information:
 *
 * - The ID of the weapon's linked skill.
 * - The numerical skill bonus from any mods/ammo/etc
 * - The category of weapon (ranged, melee, grenade so far)
 * - The cumulative (not individual) damage, in the form of:
 *   - Number of d10s
 *   - Modifier(s) and operator(s) (i.e. + 10 / 5)
 *   - AP (with modifiers and operators applied)
 *   - Flag to indicate whether SOM / 10 is added
 * - The number of rounds in a magazine, if appropriate.
 * - A list of additional effects applied by the weapon/ammo/mods
 * - An average damage value based on the total.
 * - An array of supported firing mode codes (SS, SA, BF, FA)
 *
 * To derive that we need to get the data from the weapon itself, walk any mods,
 * add ammo data, then walk any ammo mods. The good news is it's not as tweaky
 * as armor in terms of where the info can come from.
 *
 * From that, templates can spit out strings that look something like:
 *
 *  [ranged] Medium Rail Pistol w/ Zero RAP Ammo (+20): 3d10 + 10 -4AP
 *           Target is screwed, +10 attack to each shot in a round
 *   [melee] Wasp Knife: 2d10 + 4 + (SOM / 10) -2AP
 *           +2d10 when used underwater or in a vacuum
 * [grenade] Sticky EMP grenade: No damage
 *           Radios w/in 10m of blast reduced to 10% range
 */
class WeaponCalculator {
  public static function total(EntityInterface $entity) {
    $data = [
      'linked_skill' => NULL,
      'skill_bonus' => 0,
      'category' => 'weapon',
      'damage' => [
        'dice' => 0,
        'mod' => 0,
        'mod_operation' => '+',
        'multiplier' => 1,
        'ap' => 0,
        'average' => 0,
        'effects' => [],
      ],
      'rounds' => 0,
      'modes' => [],
      'effects' => [],
    ];

    // If we're in an ECK entity, dance around a bit.
    if ($entity->bundle() == 'weapon' && $entity->getEntityTypeId() == 'node') {
      $weapon = $entity;
    }
    else {
      $weapon = $entity->field_weapon->entity;
    }

    if (!$weapon->field_linked_skill->isEmpty()) {
      $data['linked_skill'] = $weapon->field_linked_skill->target_id;
      switch ($weapon->field_linked_skill->entity->label()) {
        case 'Kinetic Weapons':
        case 'Beam Weapons':
        case 'Spray Weapons':
        case 'Seeker Weapons':
        case 'Exotic Ranged Weapons':
          $data['category'] = 'ranged';
          break;
        case 'Melee Weapons':
        case 'Unarmed Combat':
        case 'Blades':
        case 'Clubs':
        case 'Exotic Melee Weapons':
          $data['category'] = 'melee';
          break;
        case 'Thrown weapons':
          if (strpos($weapon->entity->label(), 'Grenade')) {
            $data['category'] = 'grenade';
          }
          else {
            $data['category'] = 'ranged';
          }
          break;
      }
    }

    foreach ($weapon->field_firing_modes as $mode) {
      $data['modes'][$mode->entity->field_lookup_code->value] = $mode->entity->label();
    }

    static::accountForItem($data, $weapon);

    foreach ($entity->field_weapon_mods as $mod) {
      static::accountForItem($data, $mod->entity);
    }

    if (!$entity->field_ammo->isEmpty()) {
      static::accountForItem($data, $entity->field_ammo->entity);
      foreach ($entity->field_ammo_mods as $ammo_mod) {
        static::accountForItem($data, $ammo_mod->entity);
      }
    }

    if ($data['damage']['mod'] < 0) {
      $data['damage']['mod_operation'] = '-';
      $data['damage']['mod'] = abs($data['damage']['mod']);
    }

    $avg = $data['damage']['dice'] * 5;
    $avg = operation_calculate_result($avg, $data['damage']['mod_operation'], $data['damage']['mod']);
    $data['damage']['average'] = intval(round($avg * $data['damage']['multiplier']));

    return $data;
  }

  public static function accountForItem(Array &$data, EntityInterface $weapon) {

    if (!$weapon->field_magazine_size->isEmpty()) {
      $data['rounds'] = operation_calculate_result($data['rounds'], $weapon->field_magazine_size->operation, $weapon->field_magazine_size->value);
    }
    if (!$weapon->field_damage_dice->isEmpty()) {
      $data['damage']['dice'] = operation_calculate_result($data['damage']['dice'], $weapon->field_damage_dice->operation, $weapon->field_damage_dice->value);
    }
    if (!$weapon->field_ap_modifier->isEmpty()) {
      $data['damage']['ap'] = operation_calculate_result($data['damage']['ap'], $weapon->field_ap_modifier->operation, $weapon->field_ap_modifier->value);
    }

    // The mod is trickier, since in theory we could get to strange stuff like
    // DV / 2 + 3 but we don't care enough to do full math handling. If we
    // encounter a multiplication or division operator, just roll with it.
    if (!$weapon->field_damage_modifier->isEmpty()) {
      switch ($weapon->field_damage_modifier->operation) {
        case '':
        case '+':
        case '-':
          $data['damage']['mod'] = operation_calculate_result($data['damage']['mod'], $weapon->field_damage_modifier->operation, $weapon->field_damage_modifier->value);
          break;
        default:
          $data['damage']['multiplier'] = operation_calculate_result($data['damage']['multiplier'], $weapon->field_damage_modifier->operation, $weapon->field_damage_modifier->value);
      }
    }

    if (!$weapon->field_skill_bonus->isEmpty()) {
      $data['skill_bonus'] += $weapon->field_skill_bonus->value;
    }

    if (!$weapon->field_special_effect->isEmpty()) {
      $data['effects'][] = $weapon->field_special_effect->value;
    }

    if (!$weapon->field_special_effect->isEmpty()) {
      $data['effects'][] = $weapon->field_special_effect->value;
    }

    foreach ($weapon->field_damage_effects as $effect) {
      if (!in_array($effect->entity->label(), $data['damage']['effects']) && (!in_array($effect->entity->label(), ['Kinetic','Energy','Melee']))) {
        $data['damage']['effects'][] = $effect->entity->label();
      }
    }
  }
}
