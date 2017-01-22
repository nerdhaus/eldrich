<?php
namespace Drupal\eldrich\Calculator;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Link;

/**
 * Class MobilityCalculator
 * @package Drupal\eldrich\Calculator
 *
 * Responsible for collecting all mobility and movement data.
 *
 * Take a given entity, walk its child fields, and produce:
 *
 * - A list of movement data, keyed by mobility system name, containing:
 *   - Mobility system entity
 *   - Movement speed
 *
 * - entity->field_mobility
 * - entity->field_movement
 * - entity->field_mobility->field_movement
 * - entity->field_augmentations->field_mobility
 * - entity->field_morph
 */
class MobilityCalculator {

  public static function total(FieldableEntityInterface $entity) {
    $mobility_systems = [];

    static::getForEntity($mobility_systems, $entity);

    foreach($mobility_systems as $key => $datum) {
      $link = Link::createFromRoute(
        $datum['entity']->label(), 'entity.lookup.canonical', ['lookup' => $datum['entity']->id()]
      )->toRenderable();
      $link['#options']['attributes']['title'] = $datum['entity']->label();
      $link['#options']['attributes']['data-toggle'] = 'popover';
      $link['#options']['attributes']['data-content'] = strip_tags($datum['entity']->field_description->value);
      $link['#suffix'] =  ' ' . $datum['walk'] . '/' . $datum['run'];
      $mobility_systems['build'][] = $link;
    }

    return $mobility_systems;
  }

  public static function getForEntity(Array &$data, FieldableEntityInterface $entity) {
    // We've got actual mobility data!
    if ($entity->hasField('field_mobility_system') && !$entity->field_mobility_system->isEmpty()) {
      $move = static::defaultData();
      $move['entity'] = $entity->field_mobility_system->entity;

      if (!$entity->field_movement_speed->isEmpty()) {
        $speed = $entity->field_movement_speed->getValue()[0];
      }
      elseif ($move['entity']->field_speed) {
        $speed = $move['entity']->field_speed->getValue()[0];
      }
      if (isset($speed)) {
        $move['walk'] = $speed['walk'];
        $move['run'] = $speed['run'];
        $move['cruise'] = $speed['cruise'];
      }

      $data[$move['entity']->label()] = $move;
    }

    // Has augmentations, which might provide additional mobility types
    if ($entity->hasField('field_augmentations')) {
      foreach ($entity->field_augmentations as $field) {
        if ($field->entity) {
          static::getForEntity($data, $field->entity);
        }
      }
    }

    // Has a morph, which provides a mobility type
    if ($entity->hasField('field_morph')) {
      foreach ($entity->field_morph as $field) {
        if ($field->entity) {
          static::getForEntity($data, $field->entity);
        }
      }
    }

    // Is a morph, with a model, with defaults for mobility
    if ($entity->hasField('field_model')) {
      foreach ($entity->field_model as $field) {
        if ($field->entity) {
          static::getForEntity($data, $field->entity);
        }
      }
    }
  }

  public static function defaultData() {
    return [
      'entity' => NULL,
      'walk' => 0,
      'run' => 0,
      'cruise' => 0,
    ];
  }
}
