<?php
/**
 * @file
 */

namespace Drupal\list_formatter\Plugin\list_formatter;

use Drupal\list_formatter\Plugin\ListFormatterListInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterInterface;

/**
 * Plugin implementation of the taxonomy module.
 *
 * @ListFormatter(
 *   id = "taxonomy",
 *   module = "taxonomy",
 *   field_types = {"taxonomy_term_reference"}
 * )
 */
class TaxonomyList implements ListFormatterListInterface {

  /**
   * @todo.
   */
  public function createList(FieldItemListInterface $items, FieldDefinitionInterface $field_definition, $langcode) {
    $settings = $display['settings'];
    $list_items = $tids = [];

    // Get an array of tids only.
    foreach ($items as $item) {
      $tids[] = $item['tid'];
    }

    $terms = Term::loadMultiple($tids);

    foreach ($items as $delta => $item) {
      // Check the term for this item has actually been loaded.
      // @see http://drupal.org/node/1281114
      if (empty($terms[$item['tid']])) {
        continue;
      }
      // Use the item name if autocreating, as there won't be a term object yet.
      $term_name = ($item['tid'] === 'autocreate') ? $item['name'] : $terms[$item['tid']]->label();
      // Check if we should display as term links or not.
      if ($settings['term_plain'] || ($item['tid'] === 'autocreate')) {
        $list_items[$delta] = check_plain($term_name);
      }
      else {
        $uri = $terms[$item['tid']]->uri();
        $list_items[$delta] = l($term_name, $uri['path']);
      }
    }

    return $list_items;
  }

  /**
   * @todo.
   */
  public function additionalSettings(&$elements, FieldDefinitionInterface $field_definition, FormatterInterface $formatter) {
    if ($field['type'] == 'taxonomy_term_reference') {
      $elements['term_plain'] = [
        '#type' => 'checkbox',
        '#title' => t("Display taxonomy terms as plain text (Not term links)."),
        '#default_value' => $formatter->getSetting('term_plain'),
      ];
    }
  }

}
