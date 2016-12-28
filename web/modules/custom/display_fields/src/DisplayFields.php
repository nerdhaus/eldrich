<?php
/**
 * @file
 * Display Fields helper class
 */

namespace Drupal\display_fields;

use Drupal\display_fields\Entity\DisplayFieldsConfig;

/**
 * Class that holds Display Fields helper functions.
 */
class DisplayFields {

  /**
   * Gets all Display Fields field plugin.
   *
   * @param $entity_type
   *   The name of the entity.
   *
   * @return array
   *   Collection of fields.
   */
  public static function getDisplayFieldsFieldDefinitions($entity_type) {
    static $static_fields;

    if (!isset($static_fields[$entity_type])) {
      foreach (\Drupal::service('plugin.manager.display_fields')->getDefinitions() as $plugin_id => $plugin) {
        $plugin['plugin_id'] = $plugin_id;
        if (empty($plugin['entity_types']) ||
            in_array($entity_type, $plugin['entity_types'])) {
        	$static_fields[$entity_type][$plugin_id] = $plugin;
        }
      }
    }

    return isset($static_fields[$entity_type]) ? $static_fields[$entity_type] : array();
  }

  /**
   * Gets all Formatters plugin.
   *
   * @param $entity_type
   *   The name of the entity.
   *
   * @return array
   *   Collection of fields.
   */
  public static function getFieldsFormatter() {
    static $static_fields;

    if (empty($static_fields)) {
      foreach (\Drupal::service('plugin.manager.field.formatter')->getDefinitions() as $plugin_id => $plugin) {
        $plugin['plugin_id'] = $plugin_id;
        $static_fields[$plugin_id] = $plugin;
      }
    }

    return !empty($static_fields) ? $static_fields : array();
  }

  /**
   * Get an instance of the display fields field plugin.
   * @param $plugin_id
   * @param $entity_type
   * @param $bundle
   * @return
   */
  public static function getDisplayFieldsField($plugin_id, $entity_type, $bundle) {
 	  $configuration = array(
      'entity_type' => $entity_type,
      'bundle' => $bundle,
    );
    // Load the plugin.
    $field_instance = \Drupal::service('plugin.manager.display_fields')->createInstance($plugin_id, $configuration);

    return $field_instance;
  }

  /**
   * Get the display fields configs attached to this entity type.
   *
   * @param $entity_type
   *   The entity_type the config are attached to.
   * @param $bundle
   *   The bundle the config are attached to.
   *
   * @return DisplayFieldsConfig
   *   The display_field_config loaded or created if they are not saved yet.
   */
  public static function getDisplayFields($entity_type, $bundle) {
 	  // Try loading the display from configuration.
    $display_fields_config = entity_load('display_fields_config', $entity_type . '.' . $bundle);

    // If not found, create a fresh display object. We do not preemptively create
    // configuration entries for each existing entity type
    // and bundle. Instead,
    // configuration entries are only created when a display object is explicitly
    // configured and saved.
    if (!$display_fields_config) {
      $display_fields_config = entity_create('display_fields_config',array(
        'targetEntityType' => $entity_type,
        'bundle' => $bundle,
        'status' => TRUE,
      ));
    }
    return $display_fields_config;
  }

  /**
   * Delete a display fields config.
   *
   * @param string $entity_type
   *   The entity type the config belongs to.
   * @param string $bundle
   *   The entity type the config belongs to.
   * @param string $field_name
   *   (Optional) You can specify a displayField machine_name
   *   to be deleted instead of deleting the whole configuration.
   */
  public static function deleteDisplayFields($entity_type, $bundle, $field_name = NULL) {
    if (empty($field_name)) {
      entity_delete_multiple('display_fields_config', array($entity_type . '.' . $bundle));
      entity_delete_multiple('display_fields_view', array($entity_type . '.' . $bundle));
    }
    else {
      $display_fields_config = self::getDisplayFields($entity_type, $bundle);
      $display_fields = $display_fields_config->get('display_fields');
      if (isset($display_fields[$field_name])) {
        unset($display_fields[$field_name]);
        $display_fields_config->set('display_fields', $display_fields);
        $display_fields_config->save();
      }
    }
  }
}
