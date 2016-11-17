<?php

namespace Drupal\operation\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\NumberWidget;

/**
 * Plugin implementation of the 'operation_string_widget' widget.
 *
 * @FieldWidget(
 *   id = "operation_string_widget",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "operation"
 *   }
 * )
 */
class OperationStringWidget extends NumberWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    if ($value = isset($items[$delta]->value)) {
      $element['type'] = 'textfield';
      $element['default_value'] = $value;
    }
    return array('value' => $element);
  }
}
