<?php

namespace Drupal\ep_skill_reference\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;

/**
 * @FieldWidget(
 *   id = "skill_reference_autocomplete",
 *   label = @Translation("Autocomplete"),
 *   description = @Translation("An autocomplete text field with skill data."),
 *   field_types = {
 *     "skill_reference"
 *   }
 * )
 */
class SkillReferenceAutocompleteWidget extends EntityReferenceAutocompleteWidget {

  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $widget = [
      '#attributes' => ['class' => ['form--inline', 'clearfix']],
      '#theme_wrappers' => ['container'],
    ];

    $widget['target_id'] = parent::formElement($items, $delta, $element, $form, $form_state);

    $widget['field'] = array(
      '#type' => 'textfield',
      '#size' => '15',
      '#default_value' => isset($items[$delta]) ? $items[$delta]->field : '',
      '#disabled' => !$items[$delta]->fieldable,
      '#placeholder' => t('Field'),
    );
    $widget['specialization'] = array(
      '#type' => 'textfield',
      '#size' => '15',
      '#default_value' => isset($items[$delta]) ? $items[$delta]->specialization : '',
      '#placeholder' => t('Specialization'),
    );
    $widget['points'] = array(
      '#type' => 'number',
      '#size' => '4',
      '#default_value' => isset($items[$delta]) ? $items[$delta]->points : 1,
      '#placeholder' => t('Points'),
    );

    $widget['target_id']['#weight'] = 0;
    $widget['field']['#weight'] = 10;
    $widget['points']['#weight'] = 20;
    $widget['specialization']['#weight'] = 30;

    return $widget;
  }
}
