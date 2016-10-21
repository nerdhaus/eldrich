<?php

namespace Drupal\ep_skill_reference;

use Drupal\Core\TypedData\TypedData;

/**
 * A computed property for Eclipse Phase stats.
 */
class SkillFieldChecker extends TypedData {
  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::getValue().
   */
  public function getValue($langcode = NULL) {
    // Soooooo ugly.
    try {
      return $this->getParent()->entity->field_fieldable->getValue();
    }
    catch(\Exception $e) {
      return true;
    }
  }
}
