<?php

namespace Drupal\eldrich\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\node\NodeStorageInterface;
use Drupal\node\NodeTypeInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Controller\NodeViewController;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Returns responses for Eldrich-specific node route variations.
 */
class CharacterViewController extends NodeViewController {

  /**
   * {@inheritdoc}
   */
  public function combatCard(EntityInterface $node) {
    $response = [];
    if (in_array($node->bundle(), ['pc', 'npc', 'creature', 'robot'])) {
      $response['primary'] = parent::view($node, 'scratchpad');

      if ($node->field_gear) {
        foreach ($node->field_gear as $fi) {
          if ($gear = $fi->entity) {
            if (in_array($gear->bundle(), ['creature', 'robot'])) {
              $response[$gear->bundle()][] = parent::view($gear, 'scratchpad');
            }
          }
        }
      }
      return $response;
    }
    else {
      throw new ResourceNotFoundException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function charSheet(EntityInterface $node) {
    if (in_array($node->bundle(), ['pc', 'npc'])){
      return parent::view($node, 'charsheet');
    }
    else {
      throw new ResourceNotFoundException();
    }
  }
}
