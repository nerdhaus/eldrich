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
    $value = print_r($item->getValue(), TRUE);
    return nl2br(Html::escape($value));
  }
}
