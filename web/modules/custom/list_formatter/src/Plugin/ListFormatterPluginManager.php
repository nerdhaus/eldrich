<?php

/**
 * @file
 * Contains \Drupal\list_formatter\Plugin\ListFormatterPluginManager.
 */

namespace Drupal\list_formatter\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Plugin type manager for all views plugins.
 */
class ListFormatterPluginManager extends DefaultPluginManager {

  /**
   * Constructs the FieldTypePluginManager object
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data_manager
   *   The typed data manager.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/list_formatter', $namespaces, $module_handler, ListFormatterListInterface::class, 'Drupal\list_formatter\Annotation\ListFormatter');
    $this->alterInfo('field_info');
    $this->setCacheBackend($cache_backend, 'list_formatter_plugins');
  }

}
