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
    // Ensures our path aliases still generate the breadcrumb trail,
    // even when taxonomy terms are being displayed.
    $container->removeDefinition('taxonomy_term.breadcrumb');
  }
}
