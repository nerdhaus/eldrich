<?php

namespace Drupal\ep_game_tools\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RenderableInterface;
use Drupal\eldrich\Calculator\SkillTreeCalculator;
use Drupal\eldrich\Calculator\StatTreeCalculator;
use Drupal\node\Entity\Node;

/**
 * Class CampaignToolsController.
 *
 * @package Drupal\ep_game_tools\Controller
 */
class CampaignToolsController extends ControllerBase {

  /**
   * Skillsheet.
   *
   * @return array
   *   Return Hello string.
   */
  public function skillsheet($nodes) {
    $raw_nids = explode(',', $nodes);
    $nids = \Drupal::entityQuery('node')
      ->condition('type', ['pc', 'npc', 'creature', 'robot'], 'IN')
      ->condition('status', TRUE)
      ->condition('nid', $raw_nids, 'IN')
      ->execute();
    $nodes = Node::loadMultiple($nids);

    $data = [];
    foreach ($nodes as $nid => $node) {
      $data[$nid]['name'] = $node->label();
      $data[$nid]['stats'] = StatTreeCalculator::total($node);
      $data[$nid]['skills'] = SkillTreeCalculator::total($node, $data[$nid]['stats']);
    }

    return [
      '#theme' => 'skillsheet',
      '#data' => $data,
    ];
  }

  /**
   * Initiative.
   *
   * @return array
   *   Return Hello string.
   */
  public function initiative($nodes) {
    $raw_nids = explode(',', $nodes);
    $nids = \Drupal::entityQuery('node')
      ->condition('type', ['pc', 'npc', 'creature', 'robot'], 'IN')
      ->condition('status', TRUE)
      ->condition('nid', $raw_nids, 'IN')
      ->execute();
    $nodes = Node::loadMultiple($nids);

    $data = [];
    foreach ($nodes as $nid => $node) {
      $data[$nid]['node'] = $node;
      $data[$nid]['stats'] = StatTreeCalculator::total($node);
    }

    return [
      '#theme' => 'initiative',
      '#data' => $data,
    ];
  }
}
