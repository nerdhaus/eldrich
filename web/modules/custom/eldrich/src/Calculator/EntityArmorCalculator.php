<?php

namespace Drupal\eldrich\Calculator;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Entity\EntityInterface;


/**
 * Class EntityArmorCalculator
 * @package Drupal\eldrich\Calculator
 *
 * Responsible for collapsing any entity's armor into single stats.
 *
 * The goal is to take a character/NPC/creature/robot/vehicle entity and produce
 * a structured array with the following information:
 *
 * - The total armor in 'x / y' form
 * - A list of additional effects applied by the armor/mods
 *
 * To derive this information, it's necessary to walk through:
 *
 * - entity->field_armor (in case it's something with native armor)
 * - entity->field_morph->field_model->field_armor
 * - entity->field_augmentations->field_armor->field_armor
 * - entity->field_equipped_armor->field_armor->field_armor
 * - entity->field_equipped_armor->field_armor_mods->field_armor
 *
 * We don't look at field_stats, which stores the synthetic nature of the shell,
 * because our morph records come pre-populated with an armor value reflecting
 * the shell's synth/bio status.
 *
 * In the future, we want to pay attention to the 'stacks' flag on each piece
 * of armor. That will make order meaningful, though, and the change will merit
 * revisiting the walk order above.
 */
class EntityArmorCalculator extends EldrichBaseCalculator {

  public static function total(EntityInterface $entity) {
    $armor = ['energy' => 0, 'kinetic' => 0];
    $effects = [];

    if (isset($entity->field_special_effect)) {
      $effects[] = $entity->field_special_effect->value;
    }

    if (isset($entity->field_morph)) {
      // If we're looking at an NPC or a Character, they have a 'morph' ECK
      // instance that stores augs and other details. Total up that sub-group.
      $data = EntityArmorCalculator::total($entity->field_morph->entity);
      $armor = $data['armor'];
      $effects = $data['effects'];
    }
    else if (isset($entity->field_model)) {
      // If there's a 'field_model' we're in an ECK entity. Grab its base armor
      // value.
      $armor['energy'] = $entity->field_model->entity->field_armor['energy'];
      $armor['kinetic'] = $entity->field_model->entity->field_armor['kinetic'];
    }
    else if (isset($entity->field_armor)) {
      // If there's a raw field_armor grab its values directly.
      $armor['energy'] = $entity->field_armor['energy'];
      $armor['kinetic'] = $entity->field_armor['kinetic'];
    }

    // Add augmentations to the mix.
    if (isset($entity->field_augmentations)) {
      foreach ($entity->field_augmentations as $aug) {
        if (isset($aug->entity->field_armor)) {
          EntityArmorCalculator::addArmor($armor, $aug->entity->field_armor);
        }
        if (isset($aug->entity->field_special_effect)) {
          $effects[] = $aug->entity->field_special_effect->value;
        }
      }
    }

    // If the entity has equipped gear, total it up.
    if (isset($entity->field_equipped_armor)) {
      $worn = ['energy' => 0, 'kinetic' => 0];
      foreach ($entity->field_equipped_armor as $eq) {
        if ($eq->entity->field_armor->entity->field_armor->replaces) {
          $worn['energy'] = $eq->entity->field_armor->entity->field_armor->energy;
          $worn['kinetic'] = $eq->entity->field_armor->entity->field_armor->kinetic;
        }
        else {
          EntityArmorCalculator::addArmor($worn, $eq->entity->field_armor->entity->field_armor);
        }
        if (isset($eq->entity->field_armor->entity->field_special_effect)) {
          $effects[] = $eq->entity->field_armor->entity->field_special_effect->value;
        }
        if (isset($eq->entity->field_armor_mods)) {
          foreach ($eq->entity->field_armor_mods as $mod) {
            EntityArmorCalculator::addArmor($worn, $mod->entity->field_armor);
            if (isset($mod->entity->entity->field_special_effect)) {
              $effects[] = $mod->entity->entity->field_special_effect->value;
            }
          }
        }
      }
    }

    return [
      'armor' => $armor,
      'effects' => $effects,
    ];
  }

  private static function addArmor(Array &$a, $b) {
    if (is_array($b)) {
      $a['energy'] += $b['energy'];
      $a['kinetic'] += $b['kinetic'];
    }
    else {
      $a['energy'] += $b->energy;
      $a['kinetic'] += $b->kinetic;
    }
  }
}
