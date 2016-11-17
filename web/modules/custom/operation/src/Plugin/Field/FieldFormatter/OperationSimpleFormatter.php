<?php

namespace Drupal\operation\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\IntegerFormatter;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'operation_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "operation_formatter",
 *   label = @Translation("String"),
 *   field_types = {
 *     "operation"
 *   }
 * )
 */
class IntegerTimeFieldFormatter extends IntegerFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    $settings = $this->getFieldSettings();

    foreach ($items as $delta => $item) {
      $output = $this->operatorFormat($item->operator) . $this->numberFormat($item->value);

      // Account for prefix and suffix.
      if ($this->getSetting('prefix_suffix')) {
        $prefixes = isset($settings['prefix']) ? array_map(array('Drupal\Core\Field\FieldFilteredMarkup', 'create'), explode('|', $settings['prefix'])) : array('');
        $suffixes = isset($settings['suffix']) ? array_map(array('Drupal\Core\Field\FieldFilteredMarkup', 'create'), explode('|', $settings['suffix'])) : array('');
        $prefix = (count($prefixes) > 1) ? $this->formatPlural($item->value, $prefixes[0], $prefixes[1]) : $prefixes[0];
        $suffix = (count($suffixes) > 1) ? $this->formatPlural($item->value, $suffixes[0], $suffixes[1]) : $suffixes[0];
        $output = $prefix . $output . $suffix;
      }
      // Output the raw value in a content attribute if the text of the HTML
      // element differs from the raw value (for example when a prefix is used).
      if (isset($item->_attributes) && $item->value != $output) {
        $item->_attributes += array('content' => $item->value);
      }

      $elements[$delta] = array('#markup' => $output);
    }

    return $elements;
  }


  /**
   * {@inheritdoc}
   */
  protected function numberFormat($number) {
    return $number;
  }

  /**
   * {@inheritdoc}
   */
  protected function operatorFormat($operation) {
    switch ($operation) {
      case '/':
        return '∕';
      case '*':
        return '×';
      case '=':
        return '';
      default:
        return $operation;
    }
  }
}