<?php
/**
 * @file
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
 *   id = "text",
 *   module = "text",
 *   field_types = {"text_default", "text_long", "text_with_summary"}
 * )
 */
class TextList implements ListFormatterListInterface {

  /**
   * @todo.
   */
  public function createList(FieldItemListInterface $items, FieldDefinitionInterface $field_definition, $langcode) {
    $settings = $display['settings'];
    $list_items = array();

    if ($field['type'] == 'text_long') {
      foreach ($items as $delta => $item) {
        // Explode on new line char, trim whitespace (if any), then array filter (So any empty lines will actually be removed).
        $long_text_items = array_filter(array_map('trim', explode("\n", $item['value'])));
        foreach ($long_text_items as $long_text_item) {
          // @see _text_sanitize(), text.module
          $list_items[] = ($instance['settings']['text_processing'] ? check_markup($long_text_item, $item['format'], $langcode) : field_filter_xss($long_text_item));
        }
      }
    }
    else {
      foreach ($items as $delta => $item) {
        $list_items[] = ($instance['settings']['text_processing'] ? check_markup($item['value'], $item['format'], $langcode) : field_filter_xss($item['value']));
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
