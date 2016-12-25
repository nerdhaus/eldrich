<?php

namespace Drupal\ep_skill_reference\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;

/**
 * Plugin implementation of the 'skill_reference_label' formatter.
 *
 * @FieldFormatter(
 *   id = "skill_reference_label",
 *   label = @Translation("Skill reference label"),
 *   description = @Translation("Display the skill name with additional data."),
 *   field_types = {
 *     "skill_reference"
 *   }
 * )
 */
class SkillReferenceLabelFormatter extends EntityReferenceLabelFormatter {

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $values = $items->getValue();

    // Matches the style used in NPC File Prime.
    // Skill Name: Field 00 (Specialization 10)
    foreach ($elements as $delta => $entity) {
      $elements[$delta]['#suffix'] = '';
      if (!empty($values[$delta]['field'])) {
        $elements[$delta]['#title'] .= ': ' . $values[$delta]['field'];
      }
      $elements[$delta]['#suffix'] .= ' ' . $values[$delta]['points'];
      if (!empty($values[$delta]['specialization'])) {
        $elements[$delta]['#suffix'] .= ' (' . $values[$delta]['specialization'] . ' ' . ($values[$delta]['points'] + 10) . ')';
      }
    }

    return $elements;
  }
}
