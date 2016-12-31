<?php
namespace Drupal\eldrich\Calculator;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;


/**
 * Class ArmorCalculator
 * @package Drupal\eldrich\Calculator
 *
 * Responsible for collapsing any entity's armor into single stats.
 *
 * The goal is to take a character/NPC/creature/robot/vehicle entity and produce
 * a structured array with the following information:
 *
 * - The total armor in 'x / y' form
 * - A list of worn gear and augmentations that contribute to the armor value
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
class ArmorCalculator {

  public static function total(EntityInterface $entity) {
    $data = static::defaultData();

    if (isset($entity->field_morph)) {
      // If we're looking at an NPC or a Character, they have a 'morph' ECK
      // instance that stores augs and other details. Total up that sub-group.
      $data = ArmorCalculator::total($entity->field_morph->entity);
    }
    else if (isset($entity->field_model)) {
      // If there's a 'field_model' we're in an ECK entity. Grab its base armor
      // value.
      $data['energy'] = $entity->field_model->entity->field_armor->energy;
      $data['kinetic'] = $entity->field_model->entity->field_armor->kinetic;
    }
    else if (isset($entity->field_armor)) {
      // If there's a raw field_armor grab its values directly.
      $data['energy'] = $entity->field_armor->energy;
      $data['kinetic'] = $entity->field_armor->kinetic;
    }

    // Add augmentations to the mix.
    if (isset($entity->field_augmentations)) {
      foreach ($entity->field_augmentations as $aug) {
        $aug = $aug->entity;
        if (!$aug->field_armor->isEmpty()) {
          $data['energy'] += $aug->field_armor->energy;
          $data['kinetic'] += $aug->field_armor->kinetic;
          $data['entities'][$aug->id()] = $aug;
          if (!$aug->field_special_effect->isEmpty()) {
            $data['effects'][] = $aug->field_special_effect->value;
          }
        }
      }
    }

    // If the entity has equipped gear, total it up.
    if (isset($entity->field_equipped_armor)) {
      $worn = static::defaultData();
      foreach ($entity->field_equipped_armor as $eq) {
        $armor = $eq->entity->field_armor->entity;
        if ($armor->field_armor->replaces) {
          $worn = static::defaultData();
        }

        $worn['energy'] += $armor->field_armor->energy;
        $worn['kinetic'] += $armor->field_armor->kinetic;
        $worn['entities'][$armor->id()] = $armor;
        if (!$armor->field_special_effect->isEmpty()) {
          $worn['effects'][] = $armor->field_special_effect->value;
        }

        foreach ($eq->entity->field_armor_mods as $mod) {
          $mod = $mod->entity;
          $worn['energy'] += $mod->field_armor->energy;
          $worn['kinetic'] += $mod->field_armor->kinetic;
          $worn['entities'][$mod->id()] = $mod;
          if (isset($mod->field_special_effect)) {
            $worn['effects'][] = $mod->field_special_effect->value;
          }
        }
      }

      $data['energy'] += $worn['energy'];
      $data['kinetic'] += $worn['kinetic'];
      foreach ($worn['effects'] as $effect) {
        $data['effects'][] = $effect;
      }
      foreach ($worn['entities'] as $id => $e) {
        $data['entities'][$id] = $e;
      }
    }

    static::linkEntities($data);
    return $data;
  }

  public static function defaultData() {
    return ['energy' => 0, 'kinetic' => 0, 'entities' => [], 'effects' => []];
  }

  public static function linkEntities(&$data) {
    foreach ($data['entities'] as $id => $entity) {
      $linkText = $entity->field_short_name->value ?: $entity->label();
      $data['build'][$id] = Link::createFromRoute($linkText, 'entity.node.canonical', ['node' => $id])->toString();
    }
  }
}
