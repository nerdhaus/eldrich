<?php

namespace Drupal\eldrich\Calculator;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Entity\EntityInterface;


/**
 * Class EquippedWeaponCalculator
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
 * - An average damage + AP value based on the total.
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
class EquippedWeaponCalculator extends EldrichBaseCalculator {
  public static function total(EntityInterface $entity) {
    $data = [
      'linked_skill' => NULL,
      'skill_bonus' => 0,
      'category' => 'weapon',
      'damage' => [
        'dice' => 0,
        'mod' => 0,
        'mod_operation' => '+',
        'ap' => 0,
        'average' => 0,
      ],
      'rounds' => 0,
      'effects' => [],
      'types' => [],
    ];

    // If we're in an ECK entity, dance around a bit.
    if ($entity->bundle() == 'weapon' && $entity->getEntityTypeId() == 'node') {
      $weapon = $entity;
    }
    else {
      $weapon = $entity->field_weapon->entity;
    }

    if (isset($weapon->field_linked_skill)) {
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

    EquippedWeaponCalculator::accountForItem($data, $weapon);

    if (!empty($entity->field_weapon_mods)) {
      foreach ($entity->field_weapon_mods as $mod) {
        EquippedWeaponCalculator::accountForItem($data, $mod->entity);
      }
    }

    if (!empty($entity->field_ammo->entity)) {
      EquippedWeaponCalculator::accountForItem($data, $entity->field_ammo->entity);
      if (!empty($entity->field_ammo_mods)) {
        foreach ($entity->field_ammo_mods as $ammo_mod) {
          EquippedWeaponCalculator::accountForItem($data, $ammo_mod->entity);
        }
      }
    }

    $avg = $data['damage']['dice'] * 5;
    $avg = operation_calculate_result($avg, $data['damage']['mod_operation'], $data['damage']['mod']);
    $data['damage']['average'] = $avg;

    return $data;
  }

  public static function accountForItem(Array &$data, EntityInterface $weapon) {
    if (!empty($weapon->field_magazine_size)) {
      $data['rounds'] = operation_calculate_result($data['rounds'], $weapon->field_magazine_size->operation, $weapon->field_magazine_size->value);
    }

    $data['damage']['dice'] = operation_calculate_result($data['damage']['dice'], $weapon->field_damage_dice->operation, $weapon->field_damage_dice->value);
    $data['damage']['ap'] = operation_calculate_result($data['damage']['ap'], $weapon->field_ap_modifier->operation, $weapon->field_ap_modifier->value);

    // The mod is trickier, since in theory we could get to strange stuff like
    // DV / 2 + 3 but we don't care enough to do full math handling. If we
    // encounter a multiplication or division operator, just roll with it.
    switch ($weapon->field_damage_modifier->operation) {
      case '':
      case '+':
      case '-':
        $data['damage']['mod'] = operation_calculate_result($data['damage']['mod'], $weapon->field_damage_modifier->operation, $weapon->field_damage_modifier->value);
        break;
      default:
        $data['damage']['mod'] = $weapon->field_damage_modifier->value;
        $data['damage']['mod_operation'] = $weapon->field_damage_modifier->operation;
    }

    if (!empty($weapon->field_skill_bonus)) {
      $data['skill_bonus'] += $weapon->field_skill_bonus->value;
    }

    if (!empty($weapon->field_special_effect)) {
      $data['effects'][] = $weapon->field_special_effect->value;
    }
  }
}
