<?php

namespace Drupal\ep_complex_values\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ep_complex_values\Plugin\Field\FieldType\ArmorValue;

/**
 * Plugin implementation of the 'armor_text' formatter.
 *
 * @FieldFormatter(
 *   id = "armor_text",
 *   label = @Translation("Armor notation"),
 *   field_types = {
 *     "armor_value",
 *   }
 * )
 */
class ArmorFormatter extends FormatterBase  {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $values = $item->getValue();
      /** @var ArmorValue $item */
      $elements[$delta] = [
        '#type' => 'markup',
        '#markup' => (empty($values['energy']) ? '-' : $values['energy']) . ' / ' . (empty($values['kinetic']) ? '-' : $values['kinetic']),
      ];
    }

    return $elements;
  }

}
