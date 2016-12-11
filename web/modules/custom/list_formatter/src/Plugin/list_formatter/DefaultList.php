<?php

/**
 * @file
 * Contains ....
 */

namespace Drupal\list_formatter\Plugin\list_formatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\list_formatter\Plugin\ListFormatterListInterface;

/**
 * Default list implementation plugin.
 *
 * @ListFormatter(
 *   id = "default",
 *   module = "list_formatter"
 * )
 */
class DefaultList implements ListFormatterListInterface {

  /**
   * Implements \Drupal\list_formatter\Plugin\ListFormatterListInterface::createList().
   */
  public function createList(FieldItemListInterface $items, FieldDefinitionInterface $field_definition, $langcode) {
    $list_items = array();

    // Use our helper function to get the value key dynamically.
    $value_key = $this->getFieldValueKey($this->field);

    foreach ($items as $delta => $item) {
      $list_items[$delta] = check_plain($item[$value_key]);
    }

    return $list_items;
  }

  /**
   * @todo.
   */
  public function additionalSettings(&$elements, FieldDefinitionInterface $field_definition, FormatterInterface $formatter) {
  }

  /**
   * Helper to return the value key for a field instance.
   *
   * @param $field array
   *  The whole array of field instance info provided by the field api.
   *
   * @return string
   *  The value key for the field.
   */
  public function getFieldValueKey(array $field) {
    return (array_key_exists('columns', $field) && is_array($field['columns'])) ? key($field['columns']) : 'value';
  }

}
