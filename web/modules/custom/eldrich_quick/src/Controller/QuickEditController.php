<?php

namespace Drupal\eldrich_quick\Controller;

use Drupal\node\NodeTypeInterface;
use Drupal\node\Controller\NodeController;

/**
 * Returns responses for Node routes.
 */
class QuickEditController extends NodeController {
    /**
   * Provides the node submission form.
   *
   * @param \Drupal\node\NodeTypeInterface $node_type
   *   The node type entity for the node.
   *
   * @return array
   *   A node submission form.
   */
  public function add(NodeTypeInterface $node_type) {

    // If it's one of the intercepted node types, handle it.
    // Otherwise pass it on to the parent.

    $node = $this->entityManager()->getStorage('node')->create(array(
      'type' => $node_type->id(),
    ));

    $form = $this->entityFormBuilder()->getForm($node);

    return $form;
  }
}
