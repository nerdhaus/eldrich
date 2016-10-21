<?php

namespace Drupal\ep_complex_values\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'armor_widget' widget.
 *
 * @FieldWidget(
 *   id = "armor_widget",
 *   label = @Translation("Default"),
 *   description = @Translation("Default"),
 *   field_types = {
 *     "armor_value"
 *   },
 * )
 */
class ArmorFieldWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
  $widget['inline'] = array(
    '#attributes' => ['class' => ['form--inline', 'clearfix']],
    '#theme_wrappers' => ['container'],
    '#value' => 'trigger',
  );

  $widget['inline']['energy'] = array(
      '#type' => 'number',
      '#title' => $this->t('Energy protection'),
      '#default_value' => $items[$delta]->energy,
    );
    $widget['inline']['kinetic'] = array(
      '#type' => 'number',
      '#attributes' => ['class' => ['clearfix']],
      '#title' => $this->t('Kinetic protection'),
      '#default_value' => $items[$delta]->kinetic,
    );
    $widget['replaces'] = array(
      '#type' => 'checkbox',
      '#attributes' => ['class' => ['clearfix']],
      '#title' => $this->t('Replaces other armor'),
      '#default_value' => !empty($items[$delta]->replaces),
    );

    return $widget;
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
