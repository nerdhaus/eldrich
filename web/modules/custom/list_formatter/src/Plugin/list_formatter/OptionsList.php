<?php

/**
 * @file
 * Contains \...
 */

namespace Drupal\list_formatter\Plugin\list_formatter;

use Drupal\list_formatter\Plugin\ListFormatterListInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterInterface;

/**
 * Plugin implementation of the taxonomy module.
 *
 * @ListFormatter(
 *   id = "options",
 *   module = "options",
 *   field_types = {"list_boolean", "list_float", "list_integer", "list_string"}
 * )
 */
class OptionsList implements ListFormatterListInterface {

  /**
   * @todo.
   */
  public function createList(FieldItemListInterface $items, FieldDefinitionInterface $field_definition, $langcode) {
    $settings = $display['settings'];
    $list_items = array();

    // Get allowed values for the field.
    $allowed_values = options_allowed_values($field);
    foreach ($items as $delta => $item) {
      if (isset($allowed_values[$item['value']])) {
        $list_items[$delta] = field_filter_xss($allowed_values[$item['value']]);
      }
    }

    return $list_items;
  }

  /**
   * @todo.
   */
  public function additionalSettings(&$elements, FieldDefinitionInterface $field_definition, FormatterInterface $formatter) {
  }

}
