<?php
/**
 * @file
 * Contains \Drupal\bootstrap\Plugin\Preprocess\Page.
 */

namespace Drupal\veil\Plugin\Preprocess;

use Drupal\bootstrap\Utility\Variables;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\bootstrap\Utility\Element;
use Drupal\eldrich\Plugin\Block\ContentCreationBlock;

/**
 * Pre-processes variables for the "user" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("user")
 */
class User extends PreprocessBase {

  /**
   * {@inheritdoc}
   */
  public function preprocessElement(Element $element, Variables $variables) {
    /** @var \Drupal\User\Entity\User $user */
    $user = $element->user;

    $variables['content_creation'] = \Drupal::service('plugin.manager.block')
      ->createInstance('content_creation_block', [])
      ->build();
  }
}
