<?php

namespace Drupal\ep_complex_values\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'weapon_range_widget' widget.
 *
 * @FieldWidget(
 *   id = "weapon_range_widget",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "weapon_range"
 *   }
 * )
 */
class WeaponRangeWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = [];

    $element = array(
      '#attributes' => ['class' => ['form--inline', 'clearfix']],
      '#theme_wrappers' => ['container'],
    );
    $element['short'] = array(
      '#type' => 'number',
      '#title' => $this->t('Short range'),
      '#default_value' => $items[$delta]->short,
    );
    $element['medium'] = array(
      '#type' => 'number',
      '#title' => $this->t('Medium (-10)'),
      '#default_value' => $items[$delta]->medium,
    );
    $element['long'] = array(
      '#type' => 'number',
      '#title' => $this->t('Long (-20)'),
      '#default_value' => $items[$delta]->long,
    );
    $element['extreme'] = array(
      '#type' => 'number',
      '#title' => $this->t('Extreme (-30)'),
      '#default_value' => $items[$delta]->extreme,
    );

    return $element;
  }
}
