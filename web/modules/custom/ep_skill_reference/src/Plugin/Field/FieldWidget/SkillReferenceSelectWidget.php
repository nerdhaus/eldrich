<?php

namespace Drupal\ep_skill_reference\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsWidgetBase;

/**
 * @FieldWidget(
 *   id = "skill_reference_select",
 *   label = @Translation("Select"),
 *   description = @Translation("Select field with associated data."),
 *   field_types = {
 *     "skill_reference"
 *   }
 * )
 */
class SkillReferenceSelectWidget extends OptionsWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element += array(
      '#type' => 'select',
      '#options' => $this->getOptions($items->getEntity()),
      '#default_value' => isset($items[$delta]->target_id) ? $items[$delta]->target_id : '',
    );

    $widget = [
      '#attributes' => ['class' => ['form--inline', 'clearfix']],
      '#theme_wrappers' => ['container'],
    ];

    $widget['target_id'] = $element;

    $widget['field'] = array(
      '#type' => 'textfield',
      '#size' => '15',
      '#default_value' => isset($items[$delta]) ? $items[$delta]->field : '',
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

  /**
   * {@inheritdoc}
   */
  protected function sanitizeLabel(&$label)
  {
    // Select form inputs allow unencoded HTML entities, but no HTML tags.
    $label = Html::decodeEntities(strip_tags($label));
  }

  /**
   * {@inheritdoc}
   */
  protected function supportsGroups()
  {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel()
  {
    // Single select: add a 'none' option for non-required fields,
    // and a 'select a value' option for required fields that do not come
    // with a value selected.
    if (!$this->required) {
      return t('- None -');
    }
    if (!$this->has_value) {
      return t('- Select a value -');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function validateElement(array $element, FormStateInterface $form_state)
  {
    if ($element['#value'] == '_none') {
      if ($element['#required'] && $element['#value'] == '_none') {
        $form_state->setError($element, t('@name field is required.', array('@name' => $element['#title'])));
      }
      else {
        $form_state->setValueForElement($element, NULL);
      }
    }
    else {
      $form_state->setValueForElement($element, $element['#value']);
    }
  }
}
