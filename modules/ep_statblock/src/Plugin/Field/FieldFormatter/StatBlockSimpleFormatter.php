<?php

namespace Drupal\ep_statblock\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'stat_block_simple' formatter.
 *
 * @FieldFormatter(
 *   id = "stat_block_simple",
 *   label = @Translation("String"),
 *   field_types = {
 *     "stat_block",
 *   }
 * )
 */
class StatBlockSimpleFormatter extends FormatterBase  {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $values = $item->getValue();
      $output = [];

      foreach ($values as $key => $value) {
        if (!empty($value)) {
          $output[] = $key . ':' . $value;
        }
      }

      $elements[$delta] = [
        '#type' => 'markup',
        '#markup' => join(', ', $output),
      ];
    }

    return $elements;
  }

}
