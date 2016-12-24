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
use Drupal\eck\Entity\EckEntity;
use Drupal\eldrich\Calculator\EquippedWeaponCalculator;
use Drupal\eldrich\Calculator\SkillTreeCalculator;
use Drupal\eldrich\Calculator\StatTreeCalculator;


/**
 * Pre-processes variables for the "eck_entity" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("eck_entity")
 */
class Eck extends PreprocessBase implements PreprocessInterface {

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables)
  {
    // Prep assorted ECK content types for display.

    /** @var $eck \Drupal\eck\Entity\EckEntity **/
    if (isset($variables->entity['#component'])) {
      $this->preprocessComponent($variables->entity['#component'], $variables);

    } elseif (isset($variables->entity['#instance'])) {
      $this->preprocessInstance($variables->entity['#instance'], $variables);

    } elseif (isset($variables->entity['#lookup'])) {
      $this->preprocessLookup($variables->entity['#lookup'], $variables);
    }
  }

  public function preprocessInstance(EckEntity $eck, Variables $variables) {
    switch ($eck->bundle()) {
      case 'weapon_instance':
        $damage = [
          'dice' => 0,
          'ap' => 0,
          'bonus' => 0,
        ];



        $variables['title'] = $eck->label();
        $variables['type'] = 'ranged';
        $variables['damage'] = '2d10 + 10 -4AP';
        $variables['bonus'] = 30;
        break;
      case 'armor_instance':
        $variables['title'] = $eck->label();
        $variables['value'] = '4 / 5';
        break;
      case 'morph':
        break;
      case 'muse':
        break;
    }
  }

  public function preprocessComponent(EckEntity $eck, Variables $variables) {
    switch ($eck->bundle()) {
      case 'identity':
        break;
      case 'attribution':
        break;
      case 'status':
        break;
    }
  }

  public function preprocessLookup(EckEntity $eck, Variables $variables) {
    // Not really anything to do here. We don't display these much.
  }
}
