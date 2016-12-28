<?php

/**
 * @file
 * Contains \Drupal\display_fields\Plugin\DisplayFieldsField\FieldReference.
 */

namespace Drupal\display_fields\Plugin\DisplayFieldsField;

use Drupal\Core\Form\FormStateInterface;
use Drupal\display_fields\Plugin\DisplayFieldsField\Field;
use Drupal\Core\Entity\Plugin\DataType\EntityReference;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\display_fields\DisplayFields;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\contextual\Element\ContextualLinks;
use Drupal\display_fields\Element\EntityFieldSelect;

/**
 * Plugin that renders a field.
 * Discover an existing field from the entity and referenced entities
 *
 * @DisplayFieldsField(
 *   id = "reference",
 *   title = @Translation("Clone a field"),
 *   entity_types = {},
 * )
 */
class FieldReference extends Field {

  /**
   * {@inheritdoc}
   */
  public function createForm($form, FormStateInterface $form_state, $parents = array()) {

    $form['display_fields_field'] = array(
      '#type' => 'entity_field_select',
      '#title' => t('Select a field'),
      '#title_display' => 'invisible',
      '#entity_type' => $this->getEntityTypeId(),
      '#bundle' => $this->bundle(),
      '#required' => TRUE,
      '#description' => t('You can clone a field from this entity and from the referenced entities.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function createFormSubmit($form, FormStateInterface $form_state, $parents = array()) {}

  /**
   * {@inheritdoc}
   */
  public function getFieldBuild($entities, $display_field, $display_settings, $parent_entity, $view_mode, $language) {
  	$build = array();
    $reference_key = $display_field['settings']['display_fields_field']['reference_key'];
  	$field_name = $display_field['settings']['display_fields_field']['field_name'];
  	$reference_key = !empty($reference_key) ? $reference_key : $field_name;
  	$display_field_name = $display_field['field_name'];

  	$reference_keys = explode(':', $reference_key);
  	$current_key = reset($reference_keys);

  	if (count($reference_keys) == 1) {
  	  // Render the fields.
  	  foreach ($entities as $delta => $entity) {
  	    if ($entity->hasField($current_key)) {
          // Get infos about this
  	      $field_definitions = \Drupal::entityManager()->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());
  	      $field_definition = $field_definitions[$current_key];
          $plugin = \Drupal::service('plugin.manager.field.formatter')->getInstance(array(
      	    'field_definition' => $field_definition,
      	    'view_mode' => $view_mode,
      	    'configuration' => !empty($display_settings) ? $display_settings : array(),
        	));
          if ($plugin) {
            $build[$display_field_name][$entity->id()][$current_key] = $entity->get($current_key)->view($display_settings);
          }
          // No plugin formatter are found for this field @TODO look at FieldItem annotation 'no ui' thing..
          else {
            // @TODO.. What to do here ?
            foreach ($entity->get($current_key) as $key => $value) {
              foreach ($value->getValue() as $key_value => $value_value) {
                $build[$display_field_name][$entity->id()][$current_key][$key_value]['#markup'] = $value_value;
              }
            }
          }
  	      $build[$display_field_name][$entity->id()]['#weight'] = $delta;
  	    }
  	  }
  	  return $build;
  	}

  	// We get here, remove a part of the reference, until we reach the end.
  	unset($reference_keys[0]);
  	$display_field['settings']['display_fields_field']['reference_key'] = implode(':', $reference_keys);

  	$current_keys = explode('|', $current_key);
  	foreach ($entities as $delta => $entity) {
  	  if ($entity->hasField($current_keys[0])) {
  	    $loaded_entities = $entity->get($current_keys[0])->referencedEntities();
  	    // Filter by bundle if specified.
  	    if ($current_keys[2] != '*') {
  	    	foreach ($loaded_entities as $delta => $loaded_entity) {
  	    		if ($loaded_entity->bundle() != $current_keys[2]) {
  	    			unset($loaded_entities[$delta]);
  	    		}
  	    	}
  	    }
  	    // Get only the first entity referenced if specified.
  	    if (isset($current_keys[3]) && $current_keys[3] == 0) {
  	      $loaded_entities = array(reset($loaded_entities));
  	    }
  	    $build[$display_field_name][$entity->id()] = $this->getFieldBuild($loaded_entities, $display_field, $display_settings, $parent_entity, $view_mode, $language);
  	    $build[$display_field_name][$entity->id()]['#weight'] = $delta;
  	  }
  	}

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildFieldFormRow($field_name, $field, $field_display_settings, $view_mode, FormStateInterface $form_state, &$complete_form) {
  	$row = parent::buildFieldFormRow($field_name, $field, $field_display_settings, $view_mode, $form_state, $complete_form);

  	$display_field_config = $field['settings']['display_fields_field'];
  	$field_definitions = \Drupal::entityManager()->getFieldDefinitions($display_field_config['entity_type'], $display_field_config['bundle']);
  	$field_definition = $field_definitions[$display_field_config['field_name']];

  	// Check the currently selected plugin, and merge persisted values for its
  	// settings.
  	if ($display_type = $form_state->getValue(array('fields', 'display_fields_' . $field_name, 'type'))) {
  	  $field_display_settings['type'] = $display_type;
  	}
  	$plugin_settings = $form_state->get('plugin_settings');
  	if (isset($plugin_settings['display_fields_' . $field_name]['settings'])) {
  	  $field_display_settings['settings'] = $plugin_settings['display_fields_' . $field_name]['settings'];
  	}

  	$plugin = \Drupal::service('plugin.manager.field.formatter')->getInstance(array(
	    'field_definition' => $field_definition,
	    'view_mode' => $view_mode,
	    'configuration' => !empty($field_display_settings) ? $field_display_settings : array(),
  	));

    if (!$plugin) {
  	  // Is not a configurable field. Just make it an extra field.
  		return $row;
  	}

  	$breadcrumb = EntityFieldSelect::getBreadcrumbReferences($display_field_config);
    $row['human_name']['#markup'] .= '<br><div class="display-field-description"><small>' . t('Cloned field:') . ' ' . str_replace('&nbsp;', ' ', strip_tags($breadcrumb)) . '</small></div>';

  	$row['#row_type'] = 'field';
  	$row['#js_settings'] = array(
        'rowHandler' => 'field',
        'defaultPlugin' => $plugin->getPluginId(),
      );

  	$row['plugin'] = array(
	    'type' => array(
        '#type' => 'select',
        '#title' => t('Plugin for @title', array('@title' => $field['label'])),
        '#title_display' => 'invisible',
        '#options' => FieldReference::getPluginOptions($field_definition) + array('hidden' => '- ' . t('Hidden') . ' -'),
        '#default_value' => !empty($field_display_settings) ? $field_display_settings['type'] : 'hidden',
        '#parents' => array('fields', 'display_fields_' . $field_name, 'type'),
        '#attributes' => array('class' => array('field-plugin-type')),
      ),
      'settings_summary' => array(),
      'settings_edit' => array(),
  	);

    // Base button element for the various plugin settings actions.
    $base_button = array(
      '#submit' => array(array($this, 'multistepSubmit')),
      '#ajax' => array(
        'callback' => array($this, 'multistepAjax'),
        'wrapper' => 'field-display-overview-wrapper',
        'effect' => 'fade',
      ),
      '#field_name' => 'display_fields_' . $field_name,
    );

    // Process values from formatters settings (these are set via ajax, so check it):
    if (!empty($field_display_settings) && $form_state->get('plugin_settings_edit') == 'display_fields_' . $field_name) {
      // We are currently editing this field's plugin settings. Display the
      // settings form and submit buttons.
      $row['plugin']['settings_edit_form'] = array();

      if ($plugin) {
        // Generate the settings form and allow other modules to alter it.
        $settings_form = $plugin->settingsForm($complete_form, $form_state);

        if ($settings_form || $third_party_settings_form) {
          $row['plugin']['#cell_attributes'] = array('colspan' => 3);
          $row['plugin']['settings_edit_form'] = array(
            '#type' => 'container',
            '#attributes' => array('class' => array('field-plugin-settings-edit-form')),
            '#parents' => array('fields', 'display_fields_' . $field_name, 'settings_edit_form'),
            'label' => array(
                '#markup' => t('Plugin settings'),
            ),
            'settings' => $settings_form,
            //'third_party_settings' => $third_party_settings_form,
            'actions' => array(
              '#type' => 'actions',
              'save_settings' => $base_button + array(
                '#type' => 'submit',
                '#button_type' => 'primary',
                '#name' => 'display_fields_' . $field_name . '_plugin_settings_update',
                '#value' => t('Update'),
                '#op' => 'update',
              ),
              'cancel_settings' => $base_button + array(
                '#type' => 'submit',
                '#name' => 'display_fields_' . $field_name . '_plugin_settings_cancel',
                '#value' => t('Cancel'),
                '#op' => 'cancel',
                // Do not check errors for the 'Cancel' button, but make sure we
                // get the value of the 'plugin type' select.
                '#limit_validation_errors' => array(array('fields', 'display_fields_' . $field_name, 'type')),
              ),
            ),
          );
          $row['#attributes']['class'][] = 'field-plugin-settings-editing';
        }
      }
    }
    elseif (!empty($field_display_settings)) {
      if ($plugin) {
        // Display a summary of the current plugin settings, and (if the
        // summary is not empty) a button to edit them.
        $summary = $plugin->settingsSummary();

        // Allow other modules to alter the summary.
        //$this->alterSettingsSummary($summary, $plugin, $field_definition);

        if (!empty($summary)) {
          $row['settings_summary'] += array(
            '#type' => 'inline_template',
            '#template' => '<div class="field-plugin-summary">{{ summary|safe_join("<br />") }}</div>',
            '#context' => array('summary' => $summary),
            '#cell_attributes' => array('class' => array('field-plugin-summary-cell')),
          );
        }

        // Check selected plugin settings to display edit link or not.
        $settings_form = $plugin->settingsForm($complete_form, $form_state);
        if (!empty($settings_form) || !empty($third_party_settings_form)) {
          $row['settings_edit'] += $base_button + array(
            '#type' => 'image_button',
            '#name' => 'display_fields_' . $field_name . '_settings_edit',
            '#src' => 'core/misc/icons/787878/cog.svg',
            '#attributes' => array('alt' => t('Edit')),
            '#op' => 'edit',
            // Do not check errors for the 'Edit' button, but make sure we get
            // the value of the 'plugin type' select.
            '#limit_validation_errors' => array(array('fields', 'display_fields_' . $field_name, 'type')),
            '#prefix' => '<div class="field-plugin-settings-edit-wrapper">',
            '#suffix' => '</div>',
          );

        }
      }
    }
    return $row;
  }

  /**
   * Form submission handler for multistep buttons.
   */
  public function multistepSubmit($form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];

    switch ($op) {
    	case 'edit':
    	  // Store the field whose settings are currently being edited.
    	  $field_name = $trigger['#field_name'];
    	  $form_state->set('plugin_settings_edit', $field_name);
    	  break;

    	case 'update':
    	  // Store the saved settings, and set the field back to 'non edit' mode.
    	  $field_name = $trigger['#field_name'];
    	  if ($plugin_settings = $form_state->getValue(array('fields', $field_name, 'settings_edit_form', 'settings'))) {
    	    $form_state->set(['plugin_settings', $field_name, 'settings'], $plugin_settings);
    	  }
    	  $form_state->set('plugin_settings_edit', NULL);
    	  break;

    	case 'cancel':
    	  // Set the field back to 'non edit' mode.
    	  $form_state->set('plugin_settings_edit', NULL);
    	  break;

    	case 'refresh_table':
    	  // If the currently edited field is one of the rows to be refreshed, set
    	  // it back to 'non edit' mode.
    	  $updated_rows = explode(' ', $form_state->getValue('refresh_rows'));
    	  $plugin_settings_edit = $form_state->get('plugin_settings_edit');
    	  if ($plugin_settings_edit && in_array($plugin_settings_edit, $updated_rows)) {

    	    $form_state->set('plugin_settings_edit', NULL);
    	  }
    	  break;
    }

    $form_state->setRebuild();
  }

  /**
   * Ajax handler for multistep buttons.
   */
  public function multistepAjax($form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];

    // Pick the elements that need to receive the ajax-new-content effect.
    switch ($op) {
    	case 'edit':
    	  $updated_rows = array($trigger['#field_name']);
    	  $updated_columns = array('plugin');
    	  break;

    	case 'update':
    	case 'cancel':
    	  $updated_rows = array($trigger['#field_name']);
    	  $updated_columns = array('plugin', 'settings_summary', 'settings_edit');
    	  break;

    	case 'refresh_table':
    	  $updated_rows = array_values(explode(' ', $form_state->getValue('refresh_rows')));
    	  $updated_columns = array('settings_summary', 'settings_edit');
    	  break;
    }

    foreach ($updated_rows as $name) {
      foreach ($updated_columns as $key) {
        $element = &$form['fields'][$name][$key];
        $element['#prefix'] = '<div class="ajax-new-content">' . (isset($element['#prefix']) ? $element['#prefix'] : '');
        $element['#suffix'] = (isset($element['#suffix']) ? $element['#suffix'] : '') . '</div>';
      }
    }

    // Return the whole table.
    return $form['fields'];
  }

  /**
   * Returns an array of widget or formatter options for a Core field.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return array
   *   An array of widget or formatter options.
   */
  protected function getPluginOptions(FieldDefinitionInterface $field_definition) {
    $options = DisplayFields::getFieldsFormatter();
    $applicable_options = array();
    foreach ($options as $option => $infos) {
      $plugin_class = DefaultFactory::getPluginClass($option, \Drupal::service('plugin.manager.field.formatter')->getDefinition($option));
      if (in_array($field_definition->getType(), $infos['field_types']) && $plugin_class::isApplicable($field_definition)) {
        $applicable_options[$option] = $infos['label'];
      }
    }
    return $applicable_options;
  }
}
