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
    if (empty($page_node) || $page_node->id != $node->id()) {
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
      foreach ($node->field_pcs as $field) {
        $nids[$field->target_id] = $field->target_id;
      }
      if (count($nids)) {
        $actions['initiative'] = [
          '#type' => 'link',
          '#title' => t('Initiative'),
          '#url' => Url::fromRoute('ep_game_tools.campaign_tools_controller_initiative', ['nodes' => join(',', $nids)]),
        ];
        $actions['skillsheet'] = [
          '#type' => 'link',
          '#title' => t('Skill Table'),
          '#url' => Url::fromRoute('ep_game_tools.campaign_tools_controller_skillsheet', ['nodes' => join(',', $nids)]),
        ];
      }
    }

    if ($variables->is_character) {
      $actions['charsheet'] = [
        '#type' => 'link',
        '#title' => t('Full Charsheet'),
        '#url' => Url::fromRoute('eldrich.charsheet', ['node' => $node->id()]),
      ];
    }

    if ($variables->is_character or $variables->is_mob) {
      $actions['scratchpad'] = [
        '#type' => 'link',
        '#title' => t('Combat Card'),
        '#url' => Url::fromRoute('eldrich.combatcard', ['node' => $node->id()]),
      ];
    }

    $type_id = $node->bundle();
    if ($variables->is_gear || $variables->is_mob || $variables->is_character || $variables->is_fluff) {
      if (\Drupal::currentUser()->hasPermission("create $type_id content")) {
        $actions['clone'] = [
          '#type' => 'link',
          '#title' => t('Clone'),
          '#url' => Url::fromRoute('eldrich.combatcard', ['node' => $node->id()]),
        ];
      }
    }

    if (count($actions)) {
      $variables->actions = $actions;
    }

  }
}