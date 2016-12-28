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
    $variables->icon = $this->getIcon($node);

    if ($variables->view_mode == 'full') {
      // Things with stats and skills
      if (in_array($node->bundle(), ['npc', 'pc', 'mind', 'robot', 'creature'])) {
        $variables['stats'] = StatTreeCalculator::total($node);
        $variables['skills'] = SkillTreeCalculator::total($node, $variables['stats']);
      }
    }
  }

  public function getIcon(NodeInterface $node) {
    $icon = NULL;
    if (!empty($node->field_gear_type)) {
      $icon = $node->field_gear_type->entity->field_icon->value;
    }
    if (empty($icon)) {
      switch ($node->bundle()) {
        case 'weapon':
          $icon = 'targeted';
          break;
        case 'gear':
          $icon = 'spanner';
          break;
        case 'creature':
          $icon = 'scorpion';
          break;
        case 'npc':
          $icon = 'meeple';
          break;
        case 'faction':
          $icon = 'anarchy';
          break;
        case 'location':
          $icon = 'world';
          break;
        case 'strain':
          $icon = 'biohazard';
          break;
        default:
          $icon = 'eclipse';
      }
    }

    return 'icon-' . $icon;
  }
}
