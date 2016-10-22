<?php

namespace Drupal\ep_complex_values;

use Drupal\Core\TypedData\TypedData;

/**
 * A computed property for Eclipse Phase damage.
 */
class DamageAverage extends TypedData {
  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::getValue().
   */
  public function getValue($langcode = NULL) {
    $values = $this->getParent()->getValue();

    $value = $values['dice'] * 5;
    if (!empty($modifier)) {
      switch ($values['mod_function']) {
        case '+':
          $value += $values['modifier'];
          break;
        case '-':
          $value -= $values['modifier'];
          break;
        case '*':
          $value *= $values['modifier'];
          break;
        case '/':
          $value = ceil($value / $values['modifier']);
          break;
        case '=':
          $value = $values['modifier'];
          break;
      }
    }
    return $value;
  }
}
