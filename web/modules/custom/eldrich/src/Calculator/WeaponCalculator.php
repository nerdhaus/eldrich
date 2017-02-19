<?php

namespace Drupal\eldrich\Calculator;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Link;


/**
 * Class WeaponCalculator
 * @package Drupal\eldrich\Calculator
 *
 * Responsible for collapsing weapon clusters into single stats.
 *
 * The goal is to take a an entity and produce a stuctured array
 * with the following information for each associated attack:
 *
 * - A presentation array, containing:
 *   - A link to the weapon (or Psi power)
 *   - An array of links to the weapon mods, if applicable
 *     In the case of Psychic Stab, we'll use a special case to handle sleights
 *     increase increase its effectiveness.
 *   - A link to the weapon's ammo, if applicable
 *   - An array of links to the ammo mods, if applicable
 *   - A formatted string representing its attack damage
 *
 * - The ID of the weapon's linked skill.
 * - The numerical skill bonus from any mods/ammo/etc
 * - The total skill used when attacking (if available)
 * - The category of weapon (ranged, melee, grenade so far)
 * - The cumulative (not individual) damage, in the form of:
 *   - Number of d10s
 *   - Optional special modifier, like 'WIL/10' for Psi attacks
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
 *     [psi] Psychic Stab w/ Ultra Stab: DV 2d10, ignores armor
 */
class WeaponCalculator {
  private static $skills;

  /*
   * This is the biggie. Feed it an entity and it produces a set of weapon
   * weapon records. It assumes the entity in question is an NPC, PC, robot,
   * creature, etc.
   */
  public static function total(FieldableEntityInterface $entity, Array $skills = []) {
    $data = [];
    if (isset($skills)) {
      static::$skills = $skills;
    }

    if ($entity->hasField('field_equipped_weapons')) {
      foreach ($entity->field_equipped_weapons as $few) {
        if ($weapon = static::totalEquippedWeapon($few->entity)) {
          $data[] = $weapon;
        }
      }
    }

    // Combat-oriented Psi powers should appear here, too.
    if ($entity->hasField('field_sleights')) {
      if ($sleight = static::totalPsiAttack($entity->field_sleights)) {
        $data[] = $sleight;
      }
    }

    // Edge case; Infomorphs and hackers with Scorchers should count.
    if ($entity->hasField('field_gear')) {
      foreach ($entity->field_gear as $fg) {
        if ($scorcher = static::totalCombatSoftware($data, $fg->entity)) {
          $data[] = $scorcher;
        }
      }
    }

    if ($entity->hasField('field_native_attacks')) {
      foreach ($entity->field_native_attacks as $fna) {
        if ($weapon = static::totalNativeAttack($data, $fna->entity)) {
          $data[] = $weapon;
        }
      }
    }

    // This is a degenerate case, we'll build a single record just for this
    // weapon's damage.
    if ($entity->bundle() == 'weapon') {
      $weapon = static::initWeaponRecord();
      static::accountForWeapon($weapon, $entity);
      $data = $weapon;
    }

    // If they have literally no weapons, and they have a body… Well, fuck.
    // Let's calc an Unarmed Attack for them. They deserve it.
    if (count($data) == 0) {
      if ($unarmed = static::unarmedFallback($data, $entity)) {
        $data[] = $unarmed;
      }
    }

    // Catch-all function designed to apply damage bonuses for oddball gear
    static::handleGearBonuses($data, $entity);

    return $data;
  }

  private static function initWeaponRecord() {
    return [
      'linked_skill' => NULL,
      'skill' => 0,
      'skill_bonus' => 0,
      'category' => 'weapon',
      'damage' => [
        'dice' => 0,
        'mod' => 0,
        'special' => '',
        'mod_operation' => '+',
        'multiplier' => 1,
        'ap' => 0,
        'average' => 0,
        'effects' => [],
      ],
      'rounds' => 0,
      'modes' => [],
      'effects' => [],
      'build' => [],
    ];
  }


  /*
   * This is currently stubbed, and waiting for the day I implement special
   * handling for the native attacks most creatures have. For the moment
   * they're treated as custom weapons with no cost and nonexistent blueprints.
   *
   * Jesus wept.
   */
  public static function totalNativeAttack(Array &$data, FieldableEntityInterface $weapon) {
    $item = static::initWeaponRecord();

    static::getWeaponCategory($item, $weapon);

    foreach ($weapon->field_firing_modes as $mode) {
      $item['modes'][$mode->entity->field_lookup_code->value] = $mode->entity->label();
    }

    if ($skill = $weapon->field_linked_skill->entity) {
      if ($skill->field_damage_bonus->value) {
        $item['damage']['special'] = 'SOM/10';
      }
    }

    if (!$weapon->field_magazine_size->isEmpty()) {
      $item['rounds'] = operation_calculate_result($item['rounds'], $weapon->field_magazine_size->operation, $weapon->field_magazine_size->value);
    }
    if (!$weapon->field_damage_dice->isEmpty()) {
      $item['damage']['dice'] = operation_calculate_result($item['damage']['dice'], $weapon->field_damage_dice->operation, $weapon->field_damage_dice->value);
    }
    if (!$weapon->field_ap_modifier->isEmpty()) {
      if ($weapon->field_ap_modifier->operation == '/') {
        $item['damage']['ap'] = '.5';
      }
      else {
        $item['damage']['ap'] = operation_calculate_result($item['damage']['ap'], $weapon->field_ap_modifier->operation, $weapon->field_ap_modifier->value);
      }
    }

    // The mod is trickier, since in theory we could get to strange stuff like
    // DV / 2 + 3 but we don't care enough to do full math handling. If we
    // encounter a multiplication or division operator, just roll with it.
    if (!$weapon->field_damage_modifier->isEmpty()) {
      switch ($weapon->field_damage_modifier->operation) {
        case '':
        case '+':
        case '-':
          $item['damage']['mod'] = operation_calculate_result($item['damage']['mod'], $weapon->field_damage_modifier->operation, $weapon->field_damage_modifier->value);
          break;
        default:
          $item['damage']['multiplier'] = operation_calculate_result($item['damage']['multiplier'], $weapon->field_damage_modifier->operation, $weapon->field_damage_modifier->value);
      }
    }

    if(isset(static::$skills)) {
      if (key_exists($item['linked_skill'], static::$skills)) {
        $skill_info = static::$skills[$item['linked_skill']];
        $item['skill'] = $item['skill_bonus'] + $skill_info['constant']['total'];
        if (!empty($skill_info['specialization'])) {
          if (strpos(strtolower($weapon->label()), strtolower($skill_info['specialization']))) {
            $item['skill'] += 10;
          }
        }
      }
    }

    if (!$weapon->field_special_effect->isEmpty()) {
      $item['effects'][] = $weapon->field_special_effect->value;
    }

    foreach ($weapon->field_damage_effects as $effect) {
      $ee = $effect->entity;
      if (empty($item['damage']['effects'][$ee->id()])) {
        $item['damage']['effects'][$ee->id()] = static::linkEntity($effect->entity, TRUE);
      }
    }

    $item['build']['weapon'] = [
      '#plain_text' => $weapon->field_name->value,
    ];

    if ($item['damage']['mod'] < 0) {
      $item['damage']['mod_operation'] = '-';
      $item['damage']['mod'] = abs($item['damage']['mod']);
    }

    $avg = $item['damage']['dice'] * 5;
    $avg = operation_calculate_result($avg, $item['damage']['mod_operation'], $item['damage']['mod']);
    $item['damage']['average'] = intval(round($avg * $item['damage']['multiplier']));

    return $item;
  }

  /*
   * This deals specifically with the 'EquippedWeapon' entity, responsible for
   * keeping a weapon, its mods, its ammo, and its smart ammo features together.
   * To get JUST a single weapon,
   */
  public static function totalEquippedWeapon(FieldableEntityInterface $entity) {
    $item = static::initWeaponRecord();

    $weapon = $entity->field_weapon->entity;
    static::getWeaponCategory($item, $weapon);
    foreach ($weapon->field_firing_modes as $mode) {
      $item['modes'][$mode->entity->field_lookup_code->value] = $mode->entity->label();
    }
    static::accountForWeapon($item, $weapon);

    $item['build']['weapon'] = static::linkEntity($weapon);

    foreach ($entity->field_weapon_mods as $mod) {
      static::accountForWeapon($item, $mod->entity);
      $item['build']['mods'][] = static::linkEntity($mod->entity);
    }

    if (!$entity->field_ammo->isEmpty()) {
      static::accountForWeapon($item, $entity->field_ammo->entity);
      $item['build']['ammo'] = static::linkEntity($entity->field_ammo->entity);
      foreach ($entity->field_ammo_mods as $ammo_mod) {
        $item['build']['ammo_mods'][] = static::linkEntity($ammo_mod->entity);
        static::accountForWeapon($item, $ammo_mod->entity);
      }
    }

    if ($item['damage']['mod'] < 0) {
      $item['damage']['mod_operation'] = '-';
      $item['damage']['mod'] = abs($item['damage']['mod']);
    }

    $avg = $item['damage']['dice'] * 5;
    $avg = operation_calculate_result($avg, $item['damage']['mod_operation'], $item['damage']['mod']);
    $item['damage']['average'] = intval(round($avg * $item['damage']['multiplier']));

    return $item;
  }

  /*
   * This basically exists to special-case the Psychic Stab attack.
   * If a Sleights field exists, we throw it here to see how whether Stab is
   * in the entity's set of sleights. In addition, we'll look for Psychic Rend,
   * since each level of it adds an extra 1d10 to Stab's damage.
   */
  private static function totalPsiAttack(FieldItemListInterface $sleights) {
    $item = static::initWeaponRecord();
    $has_stab = FALSE;
    $rend_levels = 0;

    foreach ($sleights as $ref) {
      $sleight = $ref->entity;

      switch ($sleight->label()) {
        case 'Psychic Stab':
          static::getWeaponCategory($item, $sleight);
          $item['damage']['dice'] = 1;
          $item['damage']['ap'] = 999;
          $item['damage']['special'] = 'WIL/10';
          $item['build']['weapon'] = static::linkEntity($sleight);
          $has_stab = TRUE;
          break;

        case 'Psychic Rend':
          $item['damage']['dice']++;
          $item['build']['mods'][] = static::linkEntity($sleight);
          break;
      }
    }

    if ($has_stab) {
      return $item;
    }
    else {
      return NULL;
    }
  }


  // TODO: Add scorcher attacks
  public static function totalCombatSoftware(Array &$data, FieldableEntityInterface $entity) {
    // Dummy up weapon records for any Scorchers the entity has equipped.
    return NULL;
  }

  public static function unarmedFallback(Array &$data, FieldableEntityInterface $entity) {
    $item = NULL;

    // If the entity in question has a physical body, they have SOME kind of unarmed attack.
    // Check for a morph instance, check for the underlying morph entity, then check if it's
    // an Eidelon. If not, give them this default.

    if ($entity->hasField('field_morph') && $morph_instance = $entity->field_morph->entity) {
      if ($morph_model = $morph_instance->field_model->entity) {
        if (!empty($morph_model->field_stats->dur)) {
          $item = static::initWeaponRecord();
          $item['build']['weapon'] = [
            '#plain_text' => 'Unarmed strike',
          ];
          $item['category'] = 'Melee';
          $item['linked_skill'] = 'unarmed combat';
          $item['damage']['dice'] = 1;
          $item['damage']['special'] = 'SOM/10';

          if(isset(static::$skills)) {
            if (key_exists($item['linked_skill'], static::$skills)) {
              $skill_info = static::$skills[$item['linked_skill']];
              $item['skill'] = $item['skill_bonus'] + $skill_info['constant']['total'];
            }
          }
        }
      }
    }

    return $item;
  }


  public static function handleGearBonuses(Array &$data, FieldableEntityInterface $entity) {
    $augs = $armor = $gear = [];

    if ($entity->hasField('field_morph')) {
      $morph = $entity->field_morph->entity;
      $augs = $entity->field_morph->entity->field_augmentations;
    }
    if ($entity->hasField('field_augmentations')) {
      $augs = $entity->field_augmentations;
    }
    if ($entity->hasField('field_equipped_armor')) {
      $armor = $entity->field_equipped_armor;
    }
    if ($entity->hasField('field_gear')) {
      $gear = $entity->field_gear;
    }

    foreach ($augs as $fi) {
      // Pneumatic limbs add an extra damage die to unarmed attacks.
      if ($fi->entity->label() == 'Pneumatic Limbs') {
        foreach ($data as $key => $item) {
          if ($item['linked_skill'] == 'unarmed combat') {
            $data[$key]['damage']['dice'] += 1;
          }
        }
      }
    }
  }


    private static function getWeaponCategory(Array &$item, FieldableEntityInterface $weapon) {
    if ($weapon->hasField('field_linked_skill')) {
      if ($weapon->field_linked_skill->isEmpty()) {
        $item['linked_skill'] = 'unarmed combat';
      }
      else {
        $item['linked_skill'] = strtolower($weapon->field_linked_skill->entity->label());
      }
    }
    elseif ($weapon->hasField('field_psi_skill')) {
      $item['linked_skill'] = strtolower($weapon->field_psi_skill->entity->label());
    }

    switch ($item['linked_skill']) {
      case 'kinetic weapons':
      case 'beam weapons':
      case 'spray weapons':
      case 'seeker weapons':
      case 'exotic ranged weapons':
        $item['category'] = 'Ranged';
        break;

      case 'melee weapons':
      case 'unarmed combat':
      case 'blades':
      case 'clubs':
      case 'exotic melee weapons':
        $item['category'] = 'Melee';
        break;

      case 'throwing weapons':
        if (strpos($weapon->label(), 'renade')) {
          $item['category'] = 'Grenade';
        }
        else {
          $item['category'] = 'Ranged';
        }
        break;

      case 'psi assault':
        $item['category'] = "Psi";
        break;

      default:
        $item['category'] = "Attack";
    }
  }

  public static function accountForWeapon(Array &$item, FieldableEntityInterface $weapon) {
    if ($skill = $weapon->field_linked_skill->entity) {
      if ($skill->field_damage_bonus->value) {
        $item['damage']['special'] = 'SOM/10';
      }
    }

    if (!$weapon->field_magazine_size->isEmpty()) {
      $item['rounds'] = operation_calculate_result($item['rounds'], $weapon->field_magazine_size->operation, $weapon->field_magazine_size->value);
    }
    if (!$weapon->field_damage_dice->isEmpty()) {
      $item['damage']['dice'] = operation_calculate_result($item['damage']['dice'], $weapon->field_damage_dice->operation, $weapon->field_damage_dice->value);
    }
    if (!$weapon->field_ap_modifier->isEmpty()) {
      $item['damage']['ap'] = operation_calculate_result($item['damage']['ap'], $weapon->field_ap_modifier->operation, $weapon->field_ap_modifier->value);
    }

    // The mod is trickier, since in theory we could get to strange stuff like
    // DV / 2 + 3 but we don't care enough to do full math handling. If we
    // encounter a multiplication or division operator, just roll with it.
    if (!$weapon->field_damage_modifier->isEmpty()) {
      switch ($weapon->field_damage_modifier->operation) {
        case '':
        case '+':
        case '-':
          $item['damage']['mod'] = operation_calculate_result($item['damage']['mod'], $weapon->field_damage_modifier->operation, $weapon->field_damage_modifier->value);
          break;
        default:
          $item['damage']['multiplier'] = operation_calculate_result($item['damage']['multiplier'], $weapon->field_damage_modifier->operation, $weapon->field_damage_modifier->value);
      }
    }

    if (!$weapon->field_skill_bonus->isEmpty()) {
      $item['skill_bonus'] += $weapon->field_skill_bonus->value;
    }
    if(isset(static::$skills)) {
      if (key_exists($item['linked_skill'], static::$skills)) {
        $skill_info = static::$skills[$item['linked_skill']];
        $item['skill'] = $item['skill_bonus'] + $skill_info['constant']['total'];
        if (!empty($skill_info['specialization'])) {
          if (strpos(strtolower($weapon->label()), strtolower($skill_info['specialization']))) {
            $item['skill'] += 10;
          }
        }
      }
    }

    if (!$weapon->field_special_effect->isEmpty()) {
      $item['effects'][] = $weapon->field_special_effect->value;
    }

    foreach ($weapon->field_damage_effects as $effect) {
      $ee = $effect->entity;
      if (empty($item['damage']['effects'][$ee->id()])) {
        $item['damage']['effects'][$ee->id()] = static::linkEntity($effect->entity, TRUE);
      }
    }
  }

  private static function linkEntity(FieldableEntityInterface $entity, $popup = FALSE) {
    if (!empty($entity->field_short_name) && !$entity->field_short_name->isEmpty()) {
      $linkText = $entity->field_short_name->value;
    }
    else {
      $linkText = $entity->label();
    }
    $link = Link::createFromRoute($linkText, 'entity.node.canonical', ['node' => $entity->id()])->toRenderable();
    if ($popup) {
      $link['#options']['attributes']['title'] = $entity->label();
      $link['#options']['attributes']['data-toggle'] = 'popover';
      $link['#options']['attributes']['data-content'] = strip_tags($entity->field_description->value);
    }
    return $link;
  }
}
