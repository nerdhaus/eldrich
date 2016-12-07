<?php

namespace Drupal\ep_import\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate_plus\Plugin\migrate\process\EntityGenerate;
use Drupal\migrate\Row;

/**
 * This plugin generates complex entities within the process plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "reference_factory"
 * )
 *
 * @see EntityGenerate
 *
 * All the configuration from the lookup plugin applies here. In its most
 * simple form, this plugin needs no configuration. If there are fields on the
 * generated entity that are required or need some default value, that can be
 * provided via a default_values configuration option.
 *
 * Example usage with default_values configuration:
 * @code
 * destination:
 *   plugin: 'entity:node'
 * process:
 *   type:
 *     plugin: default_value
 *     default_value: page
 *   field_reference:
 *     plugin: reference_factory
 *     source: values
 *     default_values:
 *       description: Default description
 *       field_long_description: Default long description
 * @endcode
 */
class ReferenceFactory extends EntityGenerate {

  /**
   * Fabricate an entity.
   *
   * This is intended to be extended by implementing classes to provide for more
   * dynamic default values, rather than just static ones.
   *
   * @param $value
   *   Primary value to use in creation of the entity.
   *
   * @return array
   *   Entity value array.
   */
  protected function entity($value) {
    if (!is_array(($value))) {
      return parent::entity($value);
    }

    $entity_values = $value;

    if ($this->lookupBundleKey) {
      $entity_values[$this->lookupBundleKey] = $this->lookupBundle;
    }

    // Gather any static default values for properties/fields.
    if (isset($this->configuration['default_values']) && is_array($this->configuration['default_values'])) {
      foreach ($this->configuration['default_values'] as $key => $data) {
        if (empty($entity_values[$key])) {
          $entity_values[$key] = $data;
        }
      }
    }

    return $entity_values;
  }

  protected function query($value) {
    return NULL;
  }
}
