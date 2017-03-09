<?php
namespace Drupal\ep_skill_reference\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'skill_reference_longtext_widget' widget.
 *
 * @FieldWidget(
 *   id = "skill_reference_longtext_widget",
 *   label = @Translation("Free Entry"),
 *   field_types = {
 *     "skill_reference"
 *   },
 *   multiple_values = TRUE
 * )
 */


class SkillReferenceLongtextWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $default = [];
    foreach ($items as $item) {
      $default[] = ep_skill_reference_format_skill($item);
    }

    $element['value'] = $element + array(
      '#type' => 'textarea',
      '#rows' => 8,
      '#default_value' => join(", ", $default),
      '#description' => t('Skills should be entered in the format: @format', ['@format' => 'Skill: Field 00 (Specialization)'])
      );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = ep_skill_reference_parse($values);
    return $values;
  }
}