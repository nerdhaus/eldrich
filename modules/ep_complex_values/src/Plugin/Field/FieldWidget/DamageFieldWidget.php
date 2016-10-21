<?php

namespace Drupal\ep_complex_values\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'damage_widget' widget.
 *
 * @FieldWidget(
 *   id = "damage_widget",
 *   label = @Translation("Default"),
 *   description = @Translation("Default"),
 *   field_types = {
 *     "damage_value"
 *   },
 * )
 */
class DamageFieldWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = [];

    $element['inline'] = array(
      '#attributes' => ['class' => ['form--inline', 'clearfix']],
      '#theme_wrappers' => ['container'],
      '#value' => 'trigger',
    );

    $element['inline']['dice'] = array(
      '#type' => 'number',
      '#title' => $this->t('Dice'),
      '#default_value' => $items[$delta]->dice,
    );
    $element['inline']['mod_function'] = array(
      '#type' => 'select',
      '#title' => $this->t('Func'),
      '#options' => ['=' => '=', '+' => '+', '-' => '-', '/' => '/', '*' => '*'],
      '#default_value' => $items[$delta]->mod_function ? $items[$delta]->mod_function : '+',
    );
    $element['inline']['modifier'] = array(
      '#type' => 'number',
      '#title' => $this->t('Modifier'),
      '#default_value' => $items[$delta]->modifier,
    );
    $element['inline']['ap'] = array(
      '#type' => 'number',
      '#title' => $this->t('AP'),
      '#default_value' => $items[$delta]->ap,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      if (is_array($value['inline'])) {
        foreach ($value['inline'] as $key => $subvalue) {
          if (!empty($subvalue)) {
            $values[$delta][$key] = $subvalue;
          }
        }
      }
    }
    return $values;
  }
}
