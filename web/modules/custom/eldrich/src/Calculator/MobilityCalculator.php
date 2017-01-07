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

  }
}