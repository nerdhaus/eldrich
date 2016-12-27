<?php

namespace Drupal\operation\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\NumberWidget;

/**
 * Plugin implementation of the 'operation_widget' widget.
 *
 * @FieldWidget(
 *   id = "operation_widget",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "operation"
 *   }
 * )
 */
class OperationSimpleWidget extends NumberWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $widget = array(
      '#attributes' => ['class' => ['form--inline', 'clearfix']],
      '#theme_wrappers' => ['container'],
    );
    $widget['operator'] = array(
      '#title' => t('Operation'),
      '#type' => 'select',
      '#options' => operation_supported_operators(),
      '#default_value' => $items[$delta]->operator,
    );
    $widget['value'] = parent::formElement($items, $delta, $element, $form, $form_state);
    $widget['value']['#weight'] = 11;

    return $widget;
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      $values[$delta]['value'] = $value['value']['value'];
      $values[$delta]['operator'] = $value['operator'];
    }
    return $values;
  }

}
