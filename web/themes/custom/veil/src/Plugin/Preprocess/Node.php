<?php
/**
 * @file
 * Contains \Drupal\bootstrap\Plugin\Preprocess\Page.
 */

namespace Drupal\veil\Plugin\Preprocess;

use Drupal\bootstrap\Annotation\BootstrapPreprocess;
use Drupal\bootstrap\Utility\Variables;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessInterface;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\node\NodeInterface;
use Drupal\eldrich\Calculator\StatTreeCalculator;
use Drupal\eldrich\Calculator\SkillTreeCalculator;

/**
 * Pre-processes variables for the "node" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("node")
 */
class Node extends PreprocessBase implements PreprocessInterface {

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    // Prep node types for display.

    /** @var NodeInterface $node */
    $node = $variables->node;
    if ($variables->view_mode == 'full') {
      switch ($node->bundle()) {
        case 'npc':
        case 'pc':
        case 'mind':
        case 'robot':
        case 'creature':
          $variables['stats'] = StatTreeCalculator::total($node);
          $variables['skills'] = SkillTreeCalculator::total($node, $variables['stats']);
          break;
      }
    }
  }

  public function getIcon(NodeInterface $node) {

  }
}
