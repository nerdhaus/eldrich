<?php

/**
 * @file
 * Contains \Drupal\display_fields\Entity\DisplayFieldsView.
 */

namespace Drupal\display_fields\Entity;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\EntityDisplayBase;
use Drupal\Core\Entity\EntityDisplayPluginCollection;

/**
 * Configuration entity that contains display options for all components of a
 * rendered entity in a given view mode.
 *
 * @ConfigEntityType(
 *   id = "display_fields_view",
 *   label = @Translation("Display fields view settings"),
 *   entity_keys = {
 *     "id" = "id",
 *     "status" = "status"
 *   }
 * )
 */
class DisplayFieldsView extends EntityDisplayBase {

  /**
   * {@inheritdoc}
   */
  protected $displayContext = 'view';


  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    $this->pluginManager = \Drupal::service('plugin.manager.field.formatter');

    parent::__construct($values, $entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderer($field_name) {
    if (isset($this->plugins[$field_name])) {
      return $this->plugins[$field_name];
    }

    // Instantiate the formatter object from the stored display properties.
    if (($configuration = $this->getComponent($field_name)) &&
        isset($configuration['type']) &&
        ($definition = $this->getFieldDefinition($field_name))) {
      $formatter = $this->pluginManager->getInstance(array(
        'field_definition' => $definition,
        'view_mode' => $this->originalMode,
        // No need to prepare, defaults have been merged in setComponent().
        'prepare' => FALSE,
        'configuration' => $configuration
      ));
    }
    else {
      $formatter = NULL;
    }

    // Persist the formatter object.
    $this->plugins[$field_name] = $formatter;
    return $formatter;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    $configurations = array();
    foreach ($this->getComponents() as $field_name => $configuration) {
      if (!empty($configuration['type']) && ($field_definition = $this->getFieldDefinition($field_name))) {
        $configurations[$configuration['type']] = $configuration + array(
          'field_definition' => $field_definition,
          'view_mode' => $this->originalMode,
        );
      }
    }

    return array(
      'formatters' => new EntityDisplayPluginCollection($this->pluginManager, $configurations)
    );
  }


  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    // Dependencies should be recalculated on every save. This ensures stale
    // dependencies are never saved.
    if (isset($this->dependencies['enforced'])) {
      $dependencies = $this->dependencies['enforced'];
      $this->dependencies = $dependencies;
      $this->dependencies['enforced'] = $dependencies;
    }
    else {
      $this->dependencies = array();
    }

    // Configuration entities need to depend on the providers of any plugins
    // that they store the configuration for.
    foreach ($this->getPluginCollections() as $plugin_collection) {
      foreach ($plugin_collection as $instance) {
        $this->calculatePluginDependencies($instance);
      }
    }

    // Configuration entities need to depend on the providers of any third
    // parties that they store the configuration for.
    foreach ($this->getThirdPartyProviders() as $provider) {
      $this->addDependency('module', $provider);
    }

    return $this->dependencies;
  }

}
