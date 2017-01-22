<?php
/**
 * @file
 * Contains Drupal\eldrich\EldrichServiceProvider
 */

namespace Drupal\eldrich;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the language manager service.
 */
class EldrichServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides language_manager class to test domain language negotiation.
    $definition = $container->getDefinition('taxonomy_term.breadcrumb');
    $container->removeDefinition('taxonomy_term.breadcrumb');
  }
}
