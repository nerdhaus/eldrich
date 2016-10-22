<?php

namespace Drupal\ep_complex_values\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'boolean' formatter.
 *
 * @FieldFormatter(
 *   id = "field_data_formatter",
 *   label = @Translation("Single property"),
 *   field_types = {
 *     "damage_value",
 *     "armor_value",
 *     "mobility_speed",
 *     "weapon_range",
 *     "skill_reference",
 *     "stat_block",
 *   }
 * )
 */
class FieldDataFormatter extends FormatterBase  {

  public function propertyList() {
    $defs = $this->fieldDefinition->getFieldStorageDefinition()->getPropertyDefinitions();
    $options = [];
    foreach ($defs as $key => $def) {
      $options[$key] = $def->getLabel();
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $options = $this::propertyList();
    return [$options[$this->getSetting('property')]];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $options = $this::propertyList();

    $element['property'] = array(
      '#title' => t('Property to display'),
      '#type' => 'radios',
      '#options' => $options,
      '#default_value' => $this->getSetting('property'),
      '#required' => TRUE,
    );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'markup',
        '#markup' => $item->getValue($this->getSetting('property')),
      ];
    }

    return $elements;
  }
}
