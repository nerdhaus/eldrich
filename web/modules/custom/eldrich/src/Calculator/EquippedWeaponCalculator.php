<?php

namespace Drupal\eldrich\Calculator;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Entity\FieldableEntityInterface;


/**
 * Class EquippedWeaponCalculator
 * @package Drupal\eldrich\Calculator
 *
 * Responsible for collapsing weapon clusters into single stats.
 *
 * The goal is to take a weapon_instance eck entity and produce
 * a structured array with the following information:
 *
 * - The linked name of the weapon
 * - The linked name(s) of any weapon mods
 * - The linked name(s) of any ammo and ammo mods
 * - The category of weapon (ranged, melee, grenade so far)
 * - The numerical skill bonus from any mods/ammo/etc
 * - The cumulative (not individual) damage, in the form of:
 *   - Number of d10s
 *   - Modifier(s) and operator(s) (i.e. + 10 / 5)
 *   - AP (with modifiers and operators applied)
 *   - Flag to indicate whether SOM / 10 is added
 * - A list of additional effects applied by the weapon/ammo/mods
 * - An average damage + AP value based on the total.
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
class EquippedWeaponCalculator extends EldrichBaseCalculator
{

  /**
   * The Twig extension under test.
   *
   * @var \Drupal\Core\Entity\FieldableEntityInterface
   */
  protected $entity;

  protected $weapon;
  protected $weapon_mods;
  protected $ammo;
  protected $ammo_mods;

  protected $attack_bonus = 0;
  protected $damage = [
    'dice' => 0,
    'ap' => 0,
    'bonus' => 0,
  ];
  protected $effects;
  protected $extras;

  public function __construct(FieldableEntityInterface $entity)
  {
    $this->entity = $entity;
  }



}