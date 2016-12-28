<?php

/**
 * @file
 * Contains \Drupal\display_fields\Plugin\DisplayFieldsField\Field.
 */

namespace Drupal\display_fields\Plugin\DisplayFieldsField;

use Drupal\display_fields\Plugin\DisplayFieldsField\DisplayFieldsFieldBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\display_fields\DisplayFields;

/**
 * The base plugin to create display fields field.
 */
abstract class Field extends DisplayFieldsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function buildFieldFormRow($field_name, $field, $field_display_settings, $view_mode, FormStateInterface $form_state, &$complete_form) {
    $regions = array_keys($this->getFieldFormRowRegions());

    $field_row = array(
      '#attributes' => array('class' => array('draggable', 'tabledrag-leaf')),
      '#row_type' => 'extra_field',
      '#region_callback' => array($this, 'getFieldFormRowRegion'),
      '#js_settings' => array(
        'rowHandler' => 'field',
      ),
      'human_name' => array(
        '#markup' => $field['label'],
      ),
      'weight' => array(
        '#type' => 'textfield',
        '#title' => t('Weight for @title', array('@title' => $field['label'])),
        '#title_display' => 'invisible',
        '#default_value' => $field_display_settings ? $field_display_settings['weight'] : '0',
        '#size' => 3,
        '#attributes' => array('class' => array('field-weight')),
      ),
      'parent_wrapper' => array(
        'parent' => array(
          '#type' => 'select',
          '#title' => t('Label display for @title', array('@title' => $field['label'])),
          '#title_display' => 'invisible',
          '#options' => array_combine($regions, $regions),
          '#empty_value' => '',
          '#attributes' => array('class' => array('field-parent')),
          '#parents' => array('fields', 'display_fields_' . $field_name, 'parent'),
        ),
        'hidden_name' => array(
          '#type' => 'hidden',
          '#default_value' => 'display_fields_' . $field_name,
          '#attributes' => array('class' => array('field-name')),
        ),
      ),
      'label' => array(
        '#type' => 'select',
        '#title' => t('Label display for @title', array('@title' => $field['label'])),
        '#title_display' => 'invisible',
        '#options' => $this->getFieldFormFieldLabelOptions(),
        '#default_value' => $field_display_settings ? $field_display_settings['label'] : 'above',
      ),
      'plugin' => array(
        'type' => array(
          '#type' => 'select',
          '#title' => t('Visibility for @title', array('@title' => $field['label'])),
          '#title_display' => 'invisible',
          '#options' => $this->getExtraFieldVisibilityOptions(),
          '#default_value' => $field_display_settings ? 'visible' : 'hidden',
          '#parents' => array('fields', 'display_fields_' . $field_name, 'type'),
          '#attributes' => array('class' => array('field-plugin-type')),
        ),
      ),
      'settings_summary' => array(),
      'settings_edit' => array(),
      'display_fields_delete' => array(),
    );


    $field_row['display_fields_delete'] = array(
      '#type' => 'image_button',
      '#src' => drupal_get_path('module', 'display_fields') . '/misc/delete.png',
      '#name' => 'display_fields_' . $field_name . '_delete',
      '#op' => 'delete',
      '#attributes' => array('alt' => t('Delete'), 'title' => t('Delete')),
      // Do not check errors for the 'Edit' button, but make sure we get
      // the value of the 'plugin type' select.
      '#limit_validation_errors' => array(array('fields', 'display_fields_' . $field_name, 'type')),
      '#validate' => array(array($this, 'deleteDisplayFieldSubmit')),
      '#field_name' => $field_name,
    );

    return $field_row;
  }

  /**
   * Form submission handler for deleting a display field.
   */
  public function deleteDisplayFieldSubmit($form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    if ($op == 'delete') {
  	  // Store the field whose settings are currently being edited.
  	  $field_name = $trigger['#field_name'];
  	  DisplayFields::deleteDisplayFields($this->getEntityTypeId(), $this->bundle(), $field_name);
    }
  }

  /**
   * Returns the region to which a row in the display overview belongs.
   *
   * @param array $row
   *   The row element.
   *
   * @return string|null
   *   The region name this row belongs to.
   */
  public function getFieldFormRowRegion($row) {
    switch ($row['#row_type']) {
      case 'field':
      case 'extra_field':
        return ($row['plugin']['type']['#value'] == 'hidden' ? 'hidden' : 'content');
    }
  }

  /**
   * Return an array containing the default regions of the entity manage display overview settings.
   *
   * @return array
   *   An array containing the default regions.
   */
  public function getFieldFormRowRegions() {
    return array(
      'content' => array(
        'title' => t('Content'),
        'invisible' => TRUE,
        'message' => t('No field is displayed.')
      ),
      'hidden' => array(
        'title' => t('Disabled'),
        'message' => t('No field is hidden.')
      ),
    );
  }

 /**
   * Returns an array of visibility options for field labels.
   *
   * @return array
   *   An array of visibility options.
   */
  public function getFieldFormFieldLabelOptions() {
    return array(
      'above' => t('Above'),
      'inline' => t('Inline'),
      'hidden' => '- ' . t('Hidden') . ' -',
      'visually_hidden' => '- ' . t('Visually Hidden') . ' -',
    );
  }

  /**
   * Returns an array of visibility options for extra fields.
   *
   * @return array
   *   An array of visibility options.
   */
  public function getExtraFieldVisibilityOptions() {
    return array(
        'visible' => t('Visible'),
        'hidden' => '- ' . t('Hidden') . ' -',
    );
  }

}
