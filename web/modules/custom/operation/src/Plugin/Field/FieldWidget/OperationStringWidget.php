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
 *   label = @Translation("Text parser"),
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
    $value = isset($items[$delta]->value) ? $items[$delta]->value : NULL;
    $operator = isset($items[$delta]->operator) ? $items[$delta]->operator : '+';
    $field_settings = $this->getFieldSettings();

    $element += array(
      '#type' => 'textfield',
      '#default_value' => $operator . $value,
      '#placeholder' => $this->getSetting('placeholder'),
    );

    // Add prefix and suffix.
    if ($field_settings['prefix']) {
      $prefixes = explode('|', $field_settings['prefix']);
      $element['#field_prefix'] = FieldFilteredMarkup::create(array_pop($prefixes));
    }
    if ($field_settings['suffix']) {
      $suffixes = explode('|', $field_settings['suffix']);
      $element['#field_suffix'] = FieldFilteredMarkup::create(array_pop($suffixes));
    }

    return array('string_value' => $element);
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $key => $value) {
      if($value['string_value']) {
        $values[$key] = operation_parse_string($value['string_value']);
      }
    }
    return $values;
  }
}
