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

/**
 * Pre-processes variables for the "block" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("block")
 */
class Block extends PreprocessBase implements PreprocessInterface {

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    switch ($variables->plugin_id) {
      case "views_block:home_page_components-npc_finder":
      case "views_block:home_page_components-gear_finder":
        // $variables['attributes']['class'][] = 'panel panel-default';
        break;
      case "views_block:home_page_components-random_faction":
      case "views_block:home_page_components-random_strain":
      case "views_block:home_page_components-random_location":
        break;
    }
  }
}
