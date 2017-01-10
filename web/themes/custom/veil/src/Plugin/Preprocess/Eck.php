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
use Drupal\eldrich\Calculator\ArmorCalculator;
use Drupal\eldrich\Calculator\WeaponCalculator;
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
    switch ($variables->entity_type) {
      case 'component':
        $this->preprocessComponent($variables->eck_entity, $variables);
        break;
      case 'instance':
        $this->preprocessInstance($variables->eck_entity, $variables);
        break;
      case 'lookup':
        $this->preprocessLookup($variables->eck_entity, $variables);
        break;
    }
  }

  public function preprocessInstance(EckEntity $eck, Variables $variables) {
    switch ($eck->bundle()) {
      case 'weapon_instance':
        foreach (WeaponCalculator::totalEquippedWeapon($eck) as $key => $value) {
          $variables[$key] = $value;
        }
        break;
      case 'armor_instance':
        foreach (ArmorCalculator::total($eck) as $key => $value) {
          $variables[$key] = $value;
        }
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
