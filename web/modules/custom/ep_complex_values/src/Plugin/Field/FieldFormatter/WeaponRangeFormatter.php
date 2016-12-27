<?php

namespace Drupal\ep_complex_values\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'weapon_range_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "weapon_range_formatter",
 *   label = @Translation("Label"),
 *   field_types = {
 *     "weapon_range"
 *   }
 * )
 */
class WeaponRangeFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $raw = $item->getValue();
      $values = [
        'short' => $raw['short'] . 'm',
        'medium' => $raw['medium'] . 'm',
        'long' => $raw['long'] . 'm',
        'extreme' => $raw['extreme'] . 'm',
      ];
      $elements[$delta] = ['#markup' => join(' / ', $values)] ;
    }

    return $elements;
  }

}
