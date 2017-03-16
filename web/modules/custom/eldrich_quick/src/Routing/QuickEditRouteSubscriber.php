<?php

namespace Drupal\eldrich_quick\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class QuickEditRouteSubscriber.
 *
 * @package Drupal\eldrich_quick\Routing
 * Adds support for 'Quick Add' and 'Quick Edit' forms on specific node types.
 */
class QuickEditRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

  }
}
