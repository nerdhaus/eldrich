<?php

namespace Drupal\ep_complex_values\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'mobility_speed_widget' widget.
 *
 * @FieldWidget(
 *   id = "mobility_speed_widget",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "mobility_speed"
 *   }
 * )
 */
class MobilitySpeedWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = [];

    $element = array(
      '#attributes' => ['class' => ['form--inline', 'clearfix']],
      '#theme_wrappers' => ['container'],
    );
    $element['walk'] = array(
      '#type' => 'number',
      '#title' => $this->t('Walking speed'),
      '#default_value' => $items[$delta]->walk,
    );
    $element['run'] = array(
      '#type' => 'number',
      '#title' => $this->t('Running speed'),
      '#default_value' => $items[$delta]->run,
    );
    $element['cruise'] = array(
      '#type' => 'number',
      '#title' => $this->t('Cruising speed'),
      '#default_value' => $items[$delta]->cruise,
    );

    return $element;
  }

}
