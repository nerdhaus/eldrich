<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldConfigInterface;
use Drupal\field_ui\FieldUI;
use Drupal\display_fields\DisplayFields;
use Drupal\display_fields\Entity\DisplayFieldsConfig;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Adds the Display fields to the form field_ui_display.
 */
function _display_fields_form_field_ui_display(&$form, FormStateInterface $form_state) {
  global $base_root, $base_path;

  // Get the entity_type, bundle and view mode.
  //$entity_type = $form['#entity_type'];
  //$bundle = $form['#bundle'];
  //$view_mode = $form_state->getFormObject()->getEntity()->getMode();

  _display_fields_form_field_ui_create_vertical_tabs($form);

  _display_fields_form_field_ui_display_fields_tab($form, $form_state);

  _display_fields_form_field_ui_display_fields_add_fields_row($form, $form_state);
}

/**
 * Add The fields row form, retrieving the $form from the DisplayFields Field plugin.
 */
function _display_fields_form_field_ui_display_fields_add_fields_row(&$form, FormStateInterface $form_state) {
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];
  $view_mode = $form_state->getFormObject()->getEntity()->getMode();

  $display_fields_config = DisplayFields::getDisplayFields($entity_type, $bundle);

  if (empty($display_fields_config->get('display_fields'))) {
  	return;
  }

  // And an header for the delete button cell
  $form['fields']['#header'][] = ' ';

  $display_fields_settings = display_fields_get_entity_view_settings($entity_type, $bundle, $view_mode);

  $form['#display_fields'] = $display_fields_config->get('display_fields');
  foreach ($form['#display_fields'] as $key => $field) {
    $field_plugin = DisplayFields::getDisplayFieldsField($field['plugin_id'], $entity_type, $bundle);

    $form['fields']['display_fields_' . $key] = $field_plugin->buildFieldFormRow($key, $field, $display_fields_settings->getComponent($key), $view_mode, $form_state, $form);

  }
  $form['actions']['submit']['#submit'][] = '_display_fields_form_field_ui_display_fields_fields_row_submit';
}

/**
 * Submit handler for the DisplayFields rows.
 * @param array $form
 * @param FormStateInterface $form_state
 */
function _display_fields_form_field_ui_display_fields_fields_row_submit(array &$form, FormStateInterface $form_state) {
  $form_values = $form_state->getValues();
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];
  $view_mode = $form_state->getFormObject()->getEntity()->getMode();
  $display_fields_settings = display_fields_get_entity_view_settings($entity_type, $bundle, $view_mode);
  // Collect data for 'regular' fields.
  foreach ($form['#display_fields'] as $field_name => $field) {
    // Retrieve the stored field settings to merge with the incoming
    // values.
    $values = $form_values['fields']['display_fields_' . $field_name];
    if ($values['type'] == 'hidden') {
      $display_fields_settings->removeComponent($field_name);
    }
    else {
      // Get plugin settings. They lie either directly in submitted form
      // values (if the whole form was submitted while some plugin settings
      // were being edited), or have been persisted in $form_state.
      $plugin_settings = $form_state->get('plugin_settings');

      $settings = array();
      if (isset($values['settings_edit_form']['settings'])) {
        $settings = $values['settings_edit_form']['settings'];
      }
      elseif (isset($plugin_settings['display_fields_' . $field_name]['settings'])) {
        $settings = $plugin_settings['display_fields_' . $field_name]['settings'];
      }
      elseif ($current_options = $display_fields_settings->getComponent($field_name)) {
        $settings = $current_options['settings'];
      }


      // Default component values.
      $component_values = array(
        'type' => $values['type'],
        'weight' => $values['weight'],
        'settings' => $settings,
      );

      // Only formatters have configurable label visibility.
      if (isset($values['label'])) {
        $component_values['label'] = $values['label'];
      }

      $display_fields_settings->setComponent($field_name, $component_values);
    }
  }

  // Save the display.
  $display_fields_settings->save();
}

/**
 * Get the vertical tab UI to create DisplayFields field.
 */
function _display_fields_form_field_ui_display_fields_tab(&$form, FormStateInterface $form_state) {

  // Get the entity_type, bundle and view mode.
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];
  $view_mode = $form_state->getFormObject()->getEntity()->getMode();
  $form_state->set('entity_type', $entity_type);
  $form_state->set('bundle', $bundle);
  $plugin_id = $form_state->getValue(array('display_fields', 'create', 'field_type'));
  $plugin_id_selected = $form_state->getValue(array('display_fields', 'create', 'plugin_id'));

  // Add layouts form.
  $form['display_fields'] = array(
    '#type' => 'details',
    '#title' => t('Display fields'),
    '#collapsible' => TRUE,
    '#group' => 'additional_settings',
    '#collapsed' => FALSE,
    '#weight' => 10,
    '#tree' => TRUE,
  );

  $form['display_fields']['create'] = array(
    '#type' => 'fieldset',
    '#prefix' => '<div id="display-fields-create-wrapper">',
    '#suffix' => '</div>',
    '#title' => t('Create a new display field'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );

  $field_type_options = display_fields_get_field_types($entity_type);
  if (empty($plugin_id) && empty($plugin_id_selected)) {

    $form['display_fields']['create']['field_type'] = array(
      '#type' => 'select',
      '#title' => t('Select the type'),
      '#options' => array(0 => '- ' . t('Select the type') . ' -') + $field_type_options,
      '#default_value' => isset($display_field_type) ? $display_field_type : null,
      '#weight' => -1,
      '#ajax' => array(
        'callback' => '_display_fields_form_field_ui_create_display_fields_callback',
        'wrapper' => 'display-fields-create-wrapper',
      ),
      '#description' => t('Select a field type to see more options.'),
      '#attributes' => array(
        'autocomplete' => 'off',
      ),
    );
  }
  else {
    // Get an instance of the field plugin.
    if (empty($plugin_id)) {
    	$plugin_id = $plugin_id_selected;
    }

    $plugin = DisplayFields::getDisplayFieldsField($plugin_id, $entity_type, $bundle);

    $form['display_fields']['create']['#title'] = $field_type_options[$plugin_id];
    $form['display_fields']['create']['plugin_form'] = array();
    $form['display_fields']['create']['plugin_form'] = $plugin->createForm($form['display_fields']['create']['plugin_form'], $form_state, array('display_fields', 'create', 'plugin_form'));
    $form['display_fields']['create']['plugin_form']['#tree'] = TRUE;

    $form['display_fields']['create']['plugin_id'] = array(
    	'#type' => 'value',
      '#value' => $plugin_id,
    );
    $form['display_fields']['create']['display_field_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#maxlength' => 255,
      '#description' => t('A label to display this on the field overview.'),
      '#required' => TRUE,
      '#weight' => -100,
    );
    $form['display_fields']['create']['display_field_machine_name'] = array(
      '#type' => 'machine_name',
      '#title' => t('Display field machine-name'),
      '#maxlength' => 160,
      '#description' => t('A unique name to save the display field. It must only contain lowercase letters, numbers and hyphens.'),
      '#machine_name' => array(
        'exists' => '_display_fields_field_machine_name_exists',
        'source' => array('display_fields', 'create', 'display_field_name'),
        'label' => t('Machine name'),
      ),
      '#required' => TRUE,
      '#weight' => -99,
    );
    $form['display_fields']['create']['submit'] = array(
    	'#type' => 'submit',
      '#value' => t('Save'),
      '#submit' => array('_display_fields_form_field_ui_create_display_fields_submit'),
    );
    $form['display_fields']['create']['cancel'] = array(
    	'#type' => 'submit',
      '#value' => t('Cancel'),
      '#op' => 'cancel',
      '#limit_validation_errors' => array(),
      '#ajax' => array(
        'callback' => '_display_fields_form_field_ui_create_display_fields_callback',
        'wrapper' => 'display-fields-create-wrapper',
      ),
      '#submit' => array('_display_fields_form_field_ui_create_display_fields_cancel'),
    );
  }
}

/**
 * Submit handler that cancel the creation of a DisplayFields.
 */
function _display_fields_form_field_ui_create_display_fields_cancel($form, FormStateInterface $form_state) {
	$triggering_element = $form_state->getTriggeringElement();
	if ($triggering_element['#op'] == 'cancel') {
    $form_state->unsetValue(array('display_fields', 'create', 'field_type'));
	  $form_state->unsetValue(array('display_fields', 'create', 'plugin_id'));
	}
	$form_state->setRebuild();
}

/**
 * Submit handler for the displayFields fields rows plugins settings.
 */
function _display_fields_form_field_ui_create_display_fields_submit($form, FormStateInterface $form_state) {
  $plugin_id = $form['display_fields']['create']['plugin_id']['#value'];
  // Get the entity_type, bundle and view mode.
  $entity_type = $form['#entity_type'];
  $bundle = $form['#bundle'];
  $plugin = DisplayFields::getDisplayFieldsField($plugin_id, $entity_type, $bundle);
  $plugin->createFormSubmit($form, $form_state, array('display_fields', 'create', 'plugin_form'));

  $values = $form_state->getValue(array('display_fields', 'create'));

  // Try to load the existing config for this entity_type / bundle
  $display_fields_config = DisplayFields::getDisplayFields($entity_type, $bundle);

  $display_fields = $display_fields_config->get('display_fields');

  $display_fields[$values['display_field_machine_name']] = array(
    'field_name' => $values['display_field_machine_name'],
    'label' => $values['display_field_name'],
    'plugin_id' => $plugin_id,
    'settings' => $values['plugin_form'],
  );

  $display_fields_config->set('display_fields', $display_fields);

  $display_fields_config->save();
}

/**
 * Element validate callback for machine_name Element ($value already exists ?).
 */
function _display_fields_field_machine_name_exists($value, $element, $form_state) {
  $entity_type = $form_state->get('entity_type');
  $bundle = $form_state->get('bundle');
  // Try to load the existing config for this entity_type / bundle
  $display_fields_config = DisplayFields::getDisplayFields($entity_type, $bundle);
  $display_fields = $display_fields_config->get('display_fields');
	return isset($display_fields[$value]);
}

/**
 * Ajax callback for the display fields vertical tab UI (creation of displayFields).
 */
function _display_fields_form_field_ui_create_display_fields_callback($form, FormStateInterface $form_state) {
  return $form['display_fields']['create'];
}

/**
 * Create or merge vertical tabs.
 */
function _display_fields_form_field_ui_create_vertical_tabs(&$form) {

  // Add additional settings vertical tab.
  if (!isset($form['additional_settings'])) {
    $form['additional_settings'] = array(
      '#type' => 'vertical_tabs',
      '#theme_wrappers' => array('vertical_tabs'),
      '#prefix' => '<div>',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    );
  }

  // Add the view modes statuses settings to the vertical tabs, if exists.
  $view_mode_admin_access = \Drupal::currentUser()->hasPermission('admin_view_modes') && \Drupal::moduleHandler()->moduleExists('ds_ui');
  if (isset($form['modes'])) {
    $form['modes']['#group'] = 'additional_settings';
    $form['modes']['#weight'] = -10;
    if ($view_mode_admin_access) {
      $url = \Drupal\Core\Url::fromRoute('field_ui.display_mode');
      $form['modes']['view_modes_custom']['#description'] = \Drupal::l(t('Manage display modes'), $url);
    }
  }
}
