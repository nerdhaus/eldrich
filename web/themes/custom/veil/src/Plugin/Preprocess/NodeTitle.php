<?php
/**
 * @file
 * Contains \Drupal\bootstrap\Plugin\Preprocess\Page.
 */

namespace Drupal\veil\Plugin\Preprocess;

use Drupal\bootstrap\Utility\Variables;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessInterface;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\Node\Entity\Node;
use Drupal\Core\Url;

/**
 * Pre-processes variables for the "node title" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("field__node__title")
 */
class NodeTitle extends PreprocessBase implements PreprocessInterface {

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $variables->element['#object'];

    $actions = [];
    $nids = [];

    if ($node->hasField('field_gm_only') && $node->field_gm_only->value) {
      $variables['gm'] = TRUE;
    }

    $page_node = \Drupal::routeMatch()->getParameter('node');
    if (empty($page_node) || $page_node->id() != $node->id()) {
      return;
    }

    $variables->is_gear = in_array($node->bundle(), [
      'gear',
      'armor',
      'augmentation',
      'drug',
      'mind',
      'morph',
      'robot',
      'vehicle',
      'weapon'
    ]);
    $variables->is_game = in_array($node->bundle(), [
      'campaign',
      'session',
      'pc'
    ]);
    $variables->is_mob = in_array($node->bundle(), [
      'creature',
      'robot',
      'mind',
      'npc',
      'pc'
    ]);
    $variables->is_character = in_array($node->bundle(), ['npc', 'pc']);
    $variables->is_fluff = in_array($node->bundle(), [
      'location',
      'faction',
      'strain'
    ]);

    if ($node->bundle() == 'campaign') {
      if ($node->access('update')) {
        $actions['session'] = [
          '#type' => 'link',
          '#title' => t('Add a Session'),
          '#url' => Url::fromRoute('node.add', ['node_type' => 'session'], ['query' => ['campaign' => $node->id()]]),
        ];

        $actions['invite'] = [
          '#markup' => '<a href="#" data-toggle="modal" data-target="#inviteModal">' . t('Invite a Player') . '</a>',
        ];
      }

      foreach ($node->field_pcs as $field) {
        $nids[$field->target_id] = $field->target_id;
      }
      if (count($nids)) {
        $actions['initiative'] = [
          '#type' => 'link',
          '#title' => t('Initiative Tracker'),
          '#url' =>Url::fromRoute('ep_game_tools.campaign_tools_controller_initiative', ['nodes' => join(',', $nids)]),
        ];
        $actions['skillsheet'] = [
          '#type' => 'link',
          '#title' => t('Skill Table'),
          '#url' =>Url::fromRoute('ep_game_tools.campaign_tools_controller_skillsheet', ['nodes' => join(',', $nids)]),
        ];
        $actions['scratchpad'] = [
          '#type' => 'link',
          '#title' => t('Combat Scratchpad'),
          '#url' => Url::fromRoute('views.game_tools.scratchpads', ['nodes' => join(',', $nids)])
        ];
      }
    }

    if ($variables->is_character) {
      $actions['charsheet'] = [
        '#type' => 'link',
        '#title' => t('Full Charsheet'),
        '#url' =>Url::fromRoute('eldrich.charsheet', ['node' => $node->id()]),
      ];
    }

    if ($variables->is_character or $variables->is_mob) {
      $actions['scratchpad'] = [
        '#type' => 'link',
        '#title' => t('Combat Scratchpad'),
        '#url' =>Url::fromRoute('eldrich.scratchpad', ['node' => $node->id()]),
      ];
    }

    $type_id = $node->bundle();
    if ($variables->is_gear || $variables->is_mob || $variables->is_character || $variables->is_fluff) {
      if (\Drupal::currentUser()->hasPermission("create $type_id content")) {
        $actions['clone'] = [
          '#type' => 'link',
          '#title' => t('Clone'),
          '#url' =>Url::fromRoute('eldrich.clone', ['original' => $node->id()]),
        ];

        if ($node->bundle() == 'npc') {
          $actions['clone_as'] = [
            '#type' => 'link',
            '#title' => t('Clone as full PC'),
            '#url' =>Url::fromRoute('eldrich.clone', ['original' => $node->id()], ['query' => ['target' => 'pc']]),
          ];
        }
      }
    }

    if (count($actions)) {
      $variables->actions = $actions;
    }
  }
}
