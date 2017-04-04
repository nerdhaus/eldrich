<?php

namespace Drupal\saved_query\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'preview_saved_query_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "preview_saved_query_formatter",
 *   label = @Translation("Query Preview"),
 *   field_types = {
 *     "saved_query_field"
 *   }
 * )
 */
class PreviewSavedQueryFormatter extends FormatterBase {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#prefix' => '<pre>',
        '#markup' => $this->viewValue($item),
        '#suffix' => '</pre>'
      ];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // Shamelessly stolen from Devel's dpq() function.

    $query = $item->getQuery();

    if (method_exists($query, 'preExecute')) {
      $query->preExecute();
    }
    $sql = (string) $query;
    $quoted = array();
    $connection = Database::getConnection();
    foreach ((array) $query->arguments() as $key => $val) {
      $quoted[$key] = is_null($val) ? 'NULL' : $connection->quote($val);
    }
    $sql = strtr($sql, $quoted);

    return nl2br(Html::escape($sql));
  }
}
