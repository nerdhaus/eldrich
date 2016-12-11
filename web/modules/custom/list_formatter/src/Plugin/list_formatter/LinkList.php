<?php

/**
 * @file
 * Contains ....
 */

namespace Drupal\list_formatter\Plugin\list_formatter;

use Drupal\list_formatter\Plugin\ListFormatterListInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterInterface;

/**
 * Default list implementation plugin.
 *
 * @ListFormatter(
 *   id = "link",
 *   module = "link"
 * )
 */
class LinkList implements ListFormatterListInterface {

  /**
   * Implements \Drupal\list_formatter\Plugin\ListFormatterListInterface::createList().
   */
  public function createList(FieldItemListInterface $items, FieldDefinitionInterface $field_definition, $langcode) {
    $list_items = array();

    foreach ($items as $delta => $item) {
      $contrib_settings = $display['settings']['list_formatter_contrib'];

      $list_items[] = theme('link_formatter_' . $contrib_settings['link_field_display_type'], array('element' => $item, 'field' => $instance));
    }

    return $list_items;
  }

  /**
   * @todo.
   */
  public function additionalSettings(&$elements, FieldDefinitionInterface $field_definition, FormatterInterface $formatter) {
    if ($field['type'] == 'link_field') {
      $settings = $field['settings'];
      $link_info = is_callable('link_field_formatter_info') ? link_field_formatter_info() : array();
      $form['list_formatter_contrib']['link_field_display_type'] = array(
        '#type' => 'select',
        '#title' => t('Link field formatting type'),
        '#description' => t('Select the type of link field to show in the list.'),
        '#options' => drupal_map_assoc(array_keys($link_info)),
        '#default_value' => isset($settings['list_formatter_contrib']['link_field_display_type']) ? $settings['list_formatter_contrib']['link_field_display_type'] : 'link_default',
      );
    }
  }

}
