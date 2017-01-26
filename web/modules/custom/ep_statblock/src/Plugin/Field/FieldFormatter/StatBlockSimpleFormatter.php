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
        'labels' => 'keys',
        'delimiter' => ', ',
      ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['labels'] = array(
      '#type' => 'select',
      '#options' => [
        'none' => t('None'),
        'keys' => t('Keys'),
        'labels' => t('Labels'),
      ],
      '#title' => t('Label type'),
      '#default_value' => $this->getSetting('labels'),
      '#required' => TRUE,
    );

    $elements['delimiter'] = array(
      '#type' => 'textfield',
      '#title' => t('Property delimiter'),
      '#default_value' => $this->getSetting('delimiter'),
    );


    $def = $this->fieldDefinition;
    $stg = $def->getFieldStorageDefinition();
    $class = $def->getItemDefinition()->getClass();
    $property_definitions = $class::propertyDefinitions($stg);

    $elements['visible_properties'] = array(
      '#type' => 'checkboxes',
      '#multiple' => TRUE,
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
      $summary[] = t('Show @props', array('@props' => join($properties, $this->getSetting('delimiter'))));
    }
    $label_style = $this->getSetting('labels');
    $summary[] = t('Label style: @label', array('@label' => $label_style));

    return $summary;
  }


  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $visible_properties = $this->getSetting('visible_properties');

    $def = $this->fieldDefinition;
    $stg = $def->getFieldStorageDefinition();
    $class = $def->getItemDefinition()->getClass();
    $property_definitions = $class::propertyDefinitions($stg);

    foreach ($items as $delta => $item) {
      $values = $item->getValue();
      $output = [];

      foreach ($values as $key => $value) {
        if ((empty($visible_properties) || !empty($visible_properties[$key])) && !empty($value)) {
          switch ($this->getSetting('labels')) {
            case 'none':
              $output[] = $value;
              break;
            case 'keys':
              $output[] = strtoupper($key) . ': ' . $value;
              break;
            case 'labels':
              $output[] = $property_definitions[$key]->getLabel() . ': ' . $value;
              break;
          }
        }
      }

      $elements[$delta] = [
        '#type' => 'markup',
        '#markup' => join($this->getSetting('delimiter'), $output),
      ];
    }

    return $elements;
  }
}
