<?php

namespace Drupal\ep_complex_values\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'mobility_speed_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "mobility_speed_formatter",
 *   label = @Translation("Label"),
 *   field_types = {
 *     "mobility_speed"
 *   }
 * )
 */
class MobilitySpeedFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $raw = $item->getValue();
      $values = [
        'walk' => $raw['walk'],
        'run' => $raw['run'],
        'cruise' => $raw['cruise'],
      ];
      $elements[$delta] = ['#markup' => join(' / ', $values)];
    }

    return $elements;
  }
}
