<?php
/**
 * @file
 * Contains \Drupal\bootstrap\Plugin\Preprocess\Page.
 */

namespace Drupal\veil\Plugin\Preprocess;

use Drupal\bootstrap\Utility\Variables;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessInterface;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\node\NodeInterface;
use Drupal\Core\Url;

use Drupal\eldrich\Calculator\ThreatCalculator;
use Drupal\eldrich\Calculator\WeaponCalculator;
use Drupal\eldrich\Calculator\MobilityCalculator;
use Drupal\eldrich\Calculator\StatTreeCalculator;
use Drupal\eldrich\Calculator\SkillTreeCalculator;
use Drupal\eldrich\Calculator\ArmorCalculator;



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
    /** @var NodeInterface $node */
    $node = $variables->node;
    $variables->plain_label = $node->label();

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
    $variables->is_source = in_array($node->bundle(), [
      'source',
      'campaign',
      'inspiration'
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
      'network',
      'faction',
      'strain'
    ]);

    // Convenience stuff.
    $variables->icon = $this->getIcon($node);
    $variables->badge = $this->getBadge($variables['content'], $node);

    // Prep our heavily-calculated data.
    $this->prepCalculatedData($node, $variables);

    // Prepare our weird component entities
    $this->prepChildFields($node, $variables);

  }

  public function prepChildFields(NodeInterface $node, Variables $variables) {
    if ($node->hasField('field_morph') && ($instance = $node->field_morph->entity)) {
      $variables->content += [
        'field_augmentations' => $instance->field_augmentations->view('default'),
        'field_morph_traits' => $instance->field_traits->view('default'),
        'field_appearance' => $instance->field_description->view('default'),
        'field_morph_model' => $instance->field_model->view('default'),
      ];
      if ($model = $instance->field_model->entity) {
        $variables->content['field_morph_image'] = $model->field_image->view('default');
      }
    }
    if ($node->hasField('field_identity') && $identity = $node->field_identity->entity) {
      $variables->content += [
        'field_background' => $identity->field_background->view('default'),
        'field_faction' => $identity->field_faction->view('default'),
        'field_rep' => $identity->field_rep->view('default'),
        'field_credits' => $identity->field_credits->view('default'),
      ];
    }

    $native = [];
    if ($node->hasField('field_native_attacks')) {
      foreach ($node->field_native_attacks as $delta => $fi) {
        if ($attack = $fi->entity) {
          if (!$attack->field_notes->isEmpty()) {
            $native[$attack->field_name->value] = $attack->field_notes->value;
          }
        }
      }
    }
    if ($node->hasField('field_native_abilities')) {
      foreach ($node->field_native_abilities as $delta => $fi) {
        if ($ability = $fi->entity) {
          if (!$ability->field_notes->isEmpty()) {
            $native[$ability->field_name->value] = $ability->field_notes->value;
          }
        }
      }
    }
    if (count($native)) {
      $variables->native_abilities = $native;
    }
  }

  public function prepCalculatedData(NodeInterface $node, Variables $variables) {
    $variables->stats = StatTreeCalculator::total($node);
    $variables->skills = SkillTreeCalculator::total($node, $variables->stats);
    $variables->mobility = MobilityCalculator::total($node);

    if ($node->bundle() == 'weapon') {
      $variables->attack = WeaponCalculator::total($node, $variables->skills);
      $variables->attacks = [];
    }
    else {
      $variables->attacks = WeaponCalculator::total($node, $variables->skills);
    }
    $variables->armor = ArmorCalculator::total($node);

    if ($node->hasField('field_status') && $status = $node->field_status->entity) {
      $variables->status = [
        'damage' => $status->field_damage->value,
        'wounds' => $status->field_wounds->value,
        'stress' => $status->field_stress->value,
        'trauma' => $status->field_trauma->value,
      ];
    }
    else {
      $variables->status = [
        'damage' => 0,
        'wounds' => 0,
        'stress' => 0,
        'trauma' => 0,
      ];
    }

    // $variables->threat = ThreatCalculator::total($variables->stats, $variables->skills, $variables->armor, $variables->attacks);
  }

  public function getIcon(NodeInterface $node) {
    $icon = NULL;
    if ($node->field_gear_type && !$node->field_gear_type->isEmpty()) {
      $icon = $node->field_gear_type->entity->field_icon->value;
    }
    if (empty($icon)) {
      switch ($node->bundle()) {
        case 'weapon':
          $icon = 'crosshair';
          break;
        case 'gear':
          $icon = 'cardboard-box';
          break;
        case 'creature':
          switch ($node->field_creature_type->entity->label()) {
            case 'AI':
              $icon = 'artificial-intelligence';
              break;
            case 'Alien':
              $icon = 'infested-mass';
              break;
            case 'Exhuman':
              $icon = 'cryo-chamber';
              break;
            case 'Exsurgent':
              $icon = 'biohazard';
              break;
            case 'Neogenetic':
              $icon = 'dna1';
              break;
            case 'Smart Animal':
              $icon = 'feline';
              break;
            case 'Tech':
              $icon = 'processor';
              break;
            case 'TITAN':
              $icon = 'all-seeing-eye';
              break;
            case 'Xenofauna':
              $icon = 'scorpion';
              break;
            default:
              $icon = 'police-target';
              break;
          }
          break;
        case 'npc':
          $icon = 'meeple';
          break;
        case 'faction':
          $icon = 'laurels';
          break;
        case 'location':
          $icon = 'world';
          break;
        case 'strain':
          $icon = 'biohazard';
          break;
        case 'morph':
          switch ($node->field_morph_type->entity->label()) {
            case 'Biomorph':
              $icon = 'muscular-torso';
              break;
            case 'Synthmorph':
              $icon = 'android-mask';
              break;
            case 'Pod':
              $icon = 'cyborg-face';
              break;
            case 'Uplift':
              $icon = 'octopus';
              break;
            case 'Eidelon':
              $icon = 'artificial-intelligence';
              break;
            case 'Flexbot':
              $icon = 'jigsaw-box';
              break;
          }
      }
    }

    return 'icon-' . $icon;
  }

  public function getBadge(Array $content, NodeInterface $node) {
    $badge = NULL;

    if (isset($content['field_cost'])) {
      $badge = $content['field_cost'];
    }
    switch ($node->bundle()) {
      case 'faction':
        $badge = $content['field_faction_type'];
        break;
      case 'location':
        $badge = $content['field_location_type'];
        break;
      case 'skill':
        $badge = $content['field_linked_aptitude'];
        break;
      case 'sleight':
        $badge = $content['field_sleight_level'];
        break;
      case 'derangement':
        $badge = $content['field_derangement_level'];
        break;
      case 'source':
        $badge = $content['field_source_type'];
        break;
    }

    return $badge;
  }

  public function buildCharacterLinks(NodeInterface $node, Variables $variables) {
    if ($variables->is_character) {
      $variables->charsheet_link = [
        '#type' => 'link',
        '#title' => t('View full charsheet'),
        '#url' => Url::fromRoute('eldrich.charsheet', ['node' => $node->id()]),
        '#attributes' => [
          'class' => ['btn', 'btn-default', 'btn-sm']
        ]
      ];
    }

    if ($variables->is_character or $variables->is_mob) {
      $variables->scratchpad_link = [
        '#type' => 'link',
        '#title' => t('Combat Scratchpad'),
        '#url' => Url::fromRoute('eldrich.scratchpad', ['node' => $node->id()]),
      ];
    }
  }

  public function addCampaignTools(NodeInterface $node, Variables $variables) {
    $nids = [];

    if ($node->hasField('field_pcs')) {
      foreach ($node->field_pcs as $field) {
        $nids[$field->target_id] = $field->target_id;
      }
    }

    if ($node->hasField('field_npcs')) {
      foreach ($node->field_npcs as $field) {
        $nids[$field->target_id] = $field->target_id;
      }
    }

    if (count($nids)) {
      $variables->tools['initiative'] = [
        '#type' => 'link',
        '#title' => t('Track Initiative'),
        '#url' => Url::fromRoute('ep_game_tools.campaign_tools_controller_initiative', ['nodes' => join(',', $nids)]),
        '#attributes' => [
          'class' => ['btn', 'btn-default']
        ]
      ];
      $variables->tools['skillsheet'] = [
        '#type' => 'link',
        '#title' => t('Skills Sheet'),
        '#url' => Url::fromRoute('ep_game_tools.campaign_tools_controller_skillsheet', ['nodes' => join(',', $nids)]),
        '#attributes' => [
          'class' => ['btn', 'btn-default']
        ]
      ];
      $variables->tools['scratchpad'] = [
        '#type' => 'link',
        '#title' => t('Combat Scratchpad'),
        '#url' => Url::fromRoute('view.game_tools.scratchpads', ['ids' => join(',', $nids)]),
        '#attributes' => [
          'class' => ['btn', 'btn-default']
        ]
      ];
    }
  }
}
