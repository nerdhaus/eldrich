<?php

namespace Drupal\ep_complex_values\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\ep_complex_values\Plugin\Field\FieldType\DamageValue;

/**
 * Plugin implementation of the 'boolean' formatter.
 *
 * @FieldFormatter(
 *   id = "damage_text",
 *   label = @Translation("Damage notation"),
 *   field_types = {
 *     "damage_value",
 *   }
 * )
 */
class DamageFormatter extends FormatterBase  {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'markup',
        '#markup' => DamageValue::formatDamageNotation($item->getValue()),
      ];
    }

    return $elements;
  }

}
