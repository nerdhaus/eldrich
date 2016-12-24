<?php

namespace Drupal\eldrich\Calculator;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Entity\FieldableEntityInterface;


/**
 * Class EquippedArmorCalculator
 * @package Drupal\eldrich\Calculator
 *
 * Responsible for collapsing armor into single stats.
 *
 * The goal is to take a character/NPC/creature/robot/vehicle entity and produce
 * a structured array with the following information:
 *
 * - The linked names of any worn armor
 * - The linked names of any armor augmentations
 * - The total armor in 'x / y' form
 * - A list of additional effects applied by the armor/mods
 *
 * To derive this information, it's necessary to walk through:
 *
 * - entity->field_armor
 * - entity->field_stats (is it synthetic?)
 * - entity->field_traits
 * - entity->field_augmentations->field_armor
 * - entity->field_traits->field_armor
 * - entity->field_equipped_armor->field_armor->field_armor
 * - entity->field_equipped_armor->field_armor_mods->field_armor
 *
 * We're going to blindly stack values up to the EP max of 20 / 20, and stop
 * walking the tree if we hit that. In the future, we want to pay attention
 * to the 'stacks' flag on each piece of armor. That will make order meaningful,
 * though, and the change will merit revisiting the walk order above.
 */
class EquippedArmorCalculator extends EldrichBaseCalculator
{

  public static function total(FieldableEntityInterface $entity)
  {

  }
}