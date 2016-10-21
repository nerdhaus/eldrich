<?php

namespace Drupal\ep_complex_values;

use Drupal\Core\TypedData\TypedData;
use Drupal\ep_complex_values\Plugin\Field\FieldType\DamageValue;

/**
 * A computed property for Eclipse Phase damage.
 */
class DamageFieldNotation extends TypedData {
  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::getValue().
   */
  public function getValue($langcode = NULL) {
    $values = $this->getParent()->getValue();
    return DamageValue::formatDamageNotation($values);
  }
}
