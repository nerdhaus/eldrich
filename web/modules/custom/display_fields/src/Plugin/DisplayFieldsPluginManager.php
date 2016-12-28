<?php

/**
 * @file
 * Contains \Drupal\display_fields\Plugin\DisplayFieldsPluginManager.
 */

namespace Drupal\display_fields\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin type manager for all display field plugins.
 */
class DisplayFieldsPluginManager extends DefaultPluginManager {

  /**
   * Constructs a new \Drupal\display_fields\Plugin\DisplayFieldsPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/DisplayFieldsField', $namespaces, $module_handler, 'Drupal\display_fields\Plugin\DisplayFieldsField\DisplayFieldsFieldInterface', 'Drupal\display_fields\Annotation\DisplayFieldsField');

    $this->alterInfo('display_field_fields_info');
    $this->setCacheBackend($cache_backend, 'display_field_fields_info');
  }

}
