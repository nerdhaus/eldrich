<?php

namespace Drupal\eldrich\Calculator;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Entity\FieldableEntityInterface;


/**
 * Class StatTreeCalculator
 * @package Drupal\eldrich\Calculator
 *
 * Responsible for the thankless task of calculating aggregate stats.
 *
 * The goal is to take a given entity, walk its child fields, and produce:
 *
 * - A set dictionary of stats, each keyed by stat code, subdivided into:
 *   - Baseline ego/mind stats
 *   - Baseline shells stats
 *   - Constant ego bonuses
 *   - Constant shell bonuses
 *   - Conditional ego bonuses
 *   - Conditional shell bonuses
 *   - Total constant stats
 *   - Total conditional stats
 *
 * - A flag indicating that the entity is in a synthetic shell.
 *
 * To derive this information, it's necessary to walk through:
 *
 * - entity->field_stats
 * - entity->field_traits
 * - entity->field_sleights
 * - entity->field_morph->field_stats
 * - entity->field_morph->field_traits
 * - entity->field_morph->field_traits->field_stats
 * - entity->field_morph->field_augmentations->field_stats
 * - entity->field_morph->field_augmentations->field_stats
 *
 */
class StatTreeCalculator extends EldrichBaseCalculator
{

  /**
   * The Twig extension under test.
   *
   * @var \Drupal\Core\Entity\FieldableEntityInterface
   */
  protected $entity;

  public function __construct(FieldableEntityInterface $entity)
  {
    $this->entity = $entity;
  }



}