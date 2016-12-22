<?php

namespace Drupal\eldrich\Calculator;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Entity\FieldableEntityInterface;


/**
 * Class SkillTreeCalculator
 * @package Drupal\eldrich\Calculator
 *
 * Responsible for the thankless task of adding stat and
 * gear bonuses to every skill in a list.
 */
class SkillTreeCalculator extends EldrichBaseCalculator
{
  public function __construct(FieldableEntityInterface $entity)
  {

  }
}
