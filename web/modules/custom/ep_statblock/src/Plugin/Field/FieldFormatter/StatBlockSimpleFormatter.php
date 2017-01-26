<?php

namespace Drupal\ep_statblock\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'stat_block_simple' formatter.
 *
 * @FieldFormatter(
 *   id = "stat_block_simple",
 *   label = @Translation("String"),
 *   field_types = {
 *     "stat_block",
 *   }
 * )
 */
class StatBlockSimpleFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
        'visible_properties' => NULL,
      ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $def = $this->fieldDefinition;
    $stg = $def->getFieldStorageDefinition();
    $class = $def->getItemDefinition()->getClass();

    $property_definitions = $class::propertyDefinitions($stg);

    $elements['visible_properties'] = array(
      '#type' => 'checkboxes',
      '#options' => [],
      '#title' => t('Visible properties'),
      '#default_value' => $this->getSetting('visible_properties'),
      '#required' => FALSE,
    );

    foreach ($property_definitions as $k => $d) {
      $elements['visible_properties']['#options'][$k] = $d->getLabel();
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $properties = $this->getSetting('visible_properties');
    if (empty($properties)) {
      $summary[] = t('Show all properties');
    }
    else {
      $summary[] = t('Show @props', array('@props' => join($properties, ', ')));
    }
    return $summary;
  }


  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $visible_properties = $this->getSetting('visible_properties');

    foreach ($items as $delta => $item) {
      $values = $item->getValue();
      $output = [];

      foreach ($values as $key => $value) {
        if (!empty($visible_properties[$key]) && !empty($value)) {
          $output[] = $key . ': ' . $value;
        }
      }

      $elements[$delta] = [
        '#type' => 'markup',
        '#markup' => strtoupper(join(', ', $output)),
      ];
    }

    return $elements;
  }
}
