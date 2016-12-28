<?php

/**
 * @file
 * Contains \Drupal\display_fields\Plugin\DisplayFieldsField\FieldEmbedView.
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
use Drupal\views\Views;
use Drupal\Component\Utility\Xss;
use Drupal\display_fields\Element\EntityFieldSelect;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;

/**
 * Plugin that renders a view.
 * Discover an existing field from the entity and referenced entities
 *
 * @DisplayFieldsField(
 *   id = "embed_view",
 *   title = @Translation("Embed a view"),
 *   entity_types = {},
 * )
 */
class FieldEmbedView extends Field {

  /**
   * {@inheritdoc}
   */
  public function createForm($form, FormStateInterface $form_state, $parents = array()) {

    $form['embed_view'] = array(
      '#type' => 'select',
      '#title' => t('Select a view'),
      '#options' => $this->getViewsOptions(),
      '#required' => TRUE,
      '#description' => t('Choose a view to embed, you can adjust the display settings by view mode.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function createFormSubmit($form, FormStateInterface $form_state, $parents = array()) {}

  /**
   *
   * @return array
   */
  public function getViewsOptions() {
    static $views_list = array();

    if (!empty($views_list)) {
    	return $views_list;
    }

    $target_type = 'view';

    $query = \Drupal::entityQuery($target_type);

    // Add entity-access tag.
    $query->addTag($target_type . '_access');
    $entities = $query->execute();
    if (empty($entities)) {
      return array();
    }
    $entities = entity_load_multiple($target_type, $entities);
    foreach ($entities as $entity) {
    	$views_list[$entity->id()] = $entity->label();
    }

  	return $views_list;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldBuild($entities, $display_field, $display_settings, $parent_entity, $view_mode, $language) {
    $build = array();

    foreach ($entities as $delta => $entity) {
      $view = Views::getView($display_field['settings']['embed_view']);
      $view_display = !empty($display_settings['settings']['view_display']) ? $display_settings['settings']['view_display'] : 'default';
    	$view->setDisplay($view_display);


    	$enable_ajax = !empty($display_settings['settings']['enable_ajax']) ? $display_settings['settings']['enable_ajax'] : 'default';
    	$view->setAjaxEnabled($enable_ajax);
    	// Set the url of the views to the current page for exposed filter form redirection.
      $view->override_url = Url::fromRoute('<current>');

      // Build the view arguments.
      $args = array();
      if (!empty($display_settings['settings']['arguments'])) {
        $view_object = entity_load('view', $display_field['settings']['embed_view']);
        $view_display = $view_object->getDisplay($view_display);
        foreach ($view_display['display_options']['arguments'] as $argument) {
          if (isset($display_settings['settings']['arguments'][$argument['id']])) {
            $arg_values = $this->getViewsArgumentsValues($entities, $display_field, $display_settings['settings']['arguments'][$argument['id']], $parent_entity, $view_mode, $language);
            if (!empty($arg_values)) {
              $args[] = implode('+', $arg_values);
            }
          }
        }
      }

    	$view->preExecute($args);
    	// Execute the view
    	$render = $view->display_handler->execute();

    	$view->postExecute();

      $build[$delta] = $render;
  	  $build[$delta]['#weight'] = $delta;
    }

    // As the view can be dynamic with multiple page,
    // we need to enable a different caching for this part of the entity render array,
    // and it is possible now with drupal 8.
    $build['#cache'] = array(
      'keys' => ['entity_view', $parent_entity->getEntityTypeId(), $parent_entity->id(), $display_field['name']],
      'contexts' => array('languages', 'url.query_args.pagers'),
      'tags' => array($parent_entity->getEntityTypeId() . ':' . $parent_entity->id() . ':' . $display_field['name']),
      //'max-age' => 0, Let the entity top render max age keep the priority.
    );
    return $build;
  }

  protected function getViewsArgumentsValues($entities, $display_field, $display_settings, $parent_entity, $view_mode, $language) {
    $reference_key = $display_settings['reference_key'];
  	$field_name = $display_settings['field_name'];
  	$reference_key = !empty($reference_key) ? $reference_key : $field_name;
  	$display_field_name = $display_field['field_name'];

  	$reference_keys = explode(':', $reference_key);
  	$current_key = reset($reference_keys);

  	if (count($reference_keys) == 1) {
  	  // Render the fields.
  	  foreach ($entities as $delta => $entity) {
  	    if ($entity->hasField($current_key)) {
          // @TODO.. What to do here ?
          foreach ($entity->get($current_key) as $key => $value) {
            foreach ($value->getValue() as $key_value => $value_value) {
              $args[] = $value_value;
            }
          }
  	    }
  	  }
  	  return $args;
  	}

  	unset($reference_keys[0]);
  	$display_settings['reference_key'] = implode(':', $reference_keys);

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
  	    if (isset($current_keys[3]) && $current_keys == 0) {
  	      $loaded_entities = array(reset($loaded_entities));
  	    }
  	    $args = array_merge($args, $this->getViewsArgumentsValues($loaded_entities, $display_field, $display_settings, $parent_entity, $view_mode, $language));

  	  }
  	}

    return $args;
  }

  /**
   * {@inheritdoc}
   */
  public function buildFieldFormRow($field_name, $field, $field_display_settings, $view_mode, FormStateInterface $form_state, &$complete_form) {
  	$row = parent::buildFieldFormRow($field_name, $field, $field_display_settings, $view_mode, $form_state, $complete_form);

  	$display_field_config = $field['settings'];

  	// Check the currently selected plugin, and merge persisted values for its
  	// settings.
  	if ($display_type = $form_state->getValue(array('fields', 'display_fields_' . $field_name, 'type'))) {
  	  $field_display_settings['type'] = $display_type;
  	}
  	$plugin_settings = $form_state->get('plugin_settings');
  	if (isset($plugin_settings['display_fields_' . $field_name]['settings'])) {
  	  $field_display_settings['settings'] = $plugin_settings['display_fields_' . $field_name]['settings'];
  	}


  	$row['human_name']['#markup'] .= '<br><div class="display-field-description"><small>' . t('View:') . ' ' . $field['settings']['embed_view'] . '</small></div>';

  	$row['#row_type'] = 'field';
  	$row['#js_settings'] = array(
        'rowHandler' => 'field',
        'defaultPlugin' => 'field_embed_view_formatter',
      );

  	$row['plugin'] = array(
	    'type' => array(
        '#type' => 'select',
        '#title' => t('Plugin for @title', array('@title' => $field['label'])),
        '#title_display' => 'invisible',
        '#options' => $this->getExtraFieldVisibilityOptions(),
        '#default_value' => !empty($field_display_settings) ? $field_display_settings['type'] : 'hidden',
        '#parents' => array('fields', 'display_fields_' . $field_name, 'type'),
        '#attributes' => array('class' => array('field-plugin-type')),
      ),
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


      // Generate the settings form and allow other modules to alter it.
      $settings_form = $this->fieldRowSettingsForm($complete_form, $form_state, $field, $field_display_settings);

      if ($settings_form) {
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
    else if(!empty($field_display_settings)) {

      // Display a summary of the current plugin settings, and (if the
      // summary is not empty) a button to edit them.
      $summary = $this->fieldRowSettingsSummary($complete_form, $form_state, $field, $field_display_settings);

      if (!empty($summary)) {
        $row['settings_summary'] += array(
          '#type' => 'markup',
          '#markup' => '<div class="field-plugin-summary">' . $summary . '</div>',
          '#cell_attributes' => array('class' => array('field-plugin-summary-cell')),
        );
      }

      // Check selected plugin settings to display edit link or not.
      $settings_form = $this->fieldRowSettingsForm($complete_form, $form_state, $field, $field_display_settings);
      if (!empty($settings_form)) {
        $row['settings_edit'] += $base_button + array(
          '#type' => 'image_button',
          '#name' => 'display_fields_' . $field_name . '_settings_edit',
          '#src' => 'core/misc/icons/787878/cog.svg',
          '#attributes' => array('class' => array('field-plugin-settings-edit'), 'alt' => t('Edit')),
          '#op' => 'edit',
          // Do not check errors for the 'Edit' button, but make sure we get
          // the value of the 'plugin type' select.
          '#limit_validation_errors' => array(array('fields', 'display_fields_' . $field_name, 'type')),
          '#prefix' => '<div class="field-plugin-settings-edit-wrapper">',
          '#suffix' => '</div>',
        );
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
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function fieldRowSettingsForm($form, FormStateInterface $form_state, $field, $field_display_settings) {
    $display_settings = !empty($field_display_settings['settings']) ? $field_display_settings['settings'] : array();
    $embed_view = $field['settings']['embed_view'];
    $view = entity_load('view', $embed_view);
    $display_options = array();

    if (!empty($display_settings['view_display'])) {
      $default_display_id = $display_settings['view_display'];
    }
    else {
      $default_display_id = 'default';
    }
    foreach ($view->get('display') as $display) {
      $display_options[$display['id']] = $display['display_title'];
    }


    $elements['view_display'] = array(
      '#type' => 'select',
      '#title' => t('Display'),
      '#options' => $display_options,
      '#default_value' => $default_display_id,
      '#description' => t('The view display used for render.'),
      '#weight' => 0,
    );

    $elements['enable_ajax'] = array(
      '#type' => 'checkbox',
      '#title' => t('Force ajax enabled'),
      '#default_value' => !empty($display_settings['enable_ajax']) ? $display_settings['enable_ajax'] : FALSE,
      '#weight' => 0,
    );
    $display = $view->getDisplay($default_display_id);



    $build['fields'] = array();
    $executable = $view->getExecutable();
    $executable->setDisplay($default_display_id);

    static $relationships = NULL;
    if (!isset($relationships)) {
      // Get relationship labels.
      $relationships = array();
      foreach ($executable->display_handler->getHandlers('relationship') as $id => $handler) {
        $relationships[$id] = $handler->adminLabel();
      }
    }

    $elements['arguments']['#tree'] = TRUE;
    $elements['arguments']['#prefix'] = '<h4>' . t('Arguments:') . '</h4>';
    foreach ($display['display_options']['arguments'] as $argument) {
      $handler = $executable->display_handler->getHandler('argument', $argument['id']);
      $field_name = $handler->adminLabel(TRUE);
      if (!empty($argument['relationship']) && !empty($relationships[$argument['relationship']])) {
        $field_name = '(' . $relationships[$argument['relationship']] . ') ' . $field_name;
      }
      $elements['arguments'][$argument['id']] = array(
        '#title' => $field_name,
        '#type' => 'entity_field_select',
        '#entity_type' => $this->getEntityTypeId(),
        '#bundle' => $this->bundle(),
        '#required' => FALSE,
        '#default_value' => !empty($display_settings['arguments'][$argument['id']]) ? $display_settings['arguments'][$argument['id']] : NULL,
        '#description' => t('You can set the arguments value from a field of this entity and those possible referenced entities.'),
      );
    }
    return $elements;
  }

  /**
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function fieldRowSettingsSummary($form, FormStateInterface $form_state, $field, $field_display_settings) {
    $embed_view = $field['settings']['embed_view'];
    $view = entity_load('view', $embed_view);
    $field_display_settings['settings']['view_display'] = !empty($field_display_settings['settings']['view_display']) ?
      $field_display_settings['settings']['view_display'] : 'default';

      $display = $view->getDisplay($field_display_settings['settings']['view_display']);

    $executable = $view->getExecutable();
    $executable->setDisplay($field_display_settings['settings']['view_display']);

    $arguments = '';
    if (!empty($field_display_settings['settings']['arguments'])) {
      static $relationships = NULL;
      if (!isset($relationships)) {
        // Get relationship labels.
        $relationships = array();
        foreach ($executable->display_handler->getHandlers('relationship') as $id => $handler) {
          $relationships[$id] = $handler->adminLabel();
        }
      }

      foreach ($field_display_settings['settings']['arguments'] as $id => $field_display_settings_argument) {
        $handler = $executable->display_handler->getHandler('argument', $id);
        if ($handler) {
          $field_name = $handler->adminLabel(TRUE);
          if (!empty($display['display_options']['arguments'][$id]['relationship']) && !empty($relationships[$display['display_options']['arguments'][$id]['relationship']])) {
            $field_name = '(' . $relationships[$display['display_options']['arguments'][$id]['relationship']] . ') ' . $field_name;
          }

          $arguments .= '<li>' . $field_name . ': <br />' . EntityFieldSelect::getBreadcrumbReferences($field_display_settings_argument) . '</li>';
        }
      }
    }
    $force_ajax = !empty($field_display_settings['settings']['enable_ajax']) ? (string) t('Yes') : (string) t('No');
    return implode('<br />', array(
       t('Display:') . ' ' . $field_display_settings['settings']['view_display'],
       t('Force ajax:') . ' ' . $force_ajax,
       t('Arguments:') . ' <ul>' . $arguments . '</ul>',
    ));
  }
}
