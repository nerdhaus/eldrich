<?php

namespace Drupal\saved_query\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'raw_saved_query_widget' widget.
 *
 * @FieldWidget(
 *   id = "raw_saved_query_widget",
 *   label = @Translation("Raw Query Editor"),
 *   field_types = {
 *     "saved_query_field"
 *   }
 * )
 */
class RawSavedQueryWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['entity_type'] = [
      '#title' => t('Entity type'),
      '#type' => 'select',
      '#default_value' => isset($items[$delta]->entity_type) ? $items[$delta]->entity_type : NULL,
    ];
    $element['conditions'] = [
      '#title' => t('Conditions'),
      '#type' => 'textarea',
      '#default_value' => isset($items[$delta]->conditions) ? $items[$delta]->conditions : NULL,
    ];
    $element['limit'] = [
      '#title' => t('Result limit'),
      '#type' => 'number',
      '#default_value' => isset($items[$delta]->limit) ? $items[$delta]->limit : NULL,
    ];
    $element['interval'] = [
      '#title' => t('Refresh interval'),
      '#type' => 'number',
      '#default_value' => isset($items[$delta]->interval) ? $items[$delta]->interval : NULL,
    ];
    $element['refreshed'] = [
      '#title' => t('Last refreshed'),
      '#type' => 'number',
      '#default_value' => isset($items[$delta]->refreshed) ? $items[$delta]->refreshed : NULL,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    return $values;
  }
}
