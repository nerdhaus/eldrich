<?php

namespace Drupal\eldrich\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

use Drupal\Core\Cache\Cache;

/**
 * Provides a 'ContentCreationBlock' block.
 *
 * @Block(
 *  id = "content_creation_block",
 *  admin_label = @Translation("Content creation block"),
 * )
 */
class ContentCreationBlock extends BlockBase implements ContainerFactoryPluginInterface {
  /** @var \Drupal\Core\Entity\EntityManagerInterface */
  protected $entityManager;

  /** @var \Drupal\Core\Session\AccountInterface */
  protected $currentUser;

  /** @var \Drupal\Core\Routing\RouteMatchInterface */
  protected $routeMatch;

  /** @var \Drupal\Core\Render\RendererInterface */
  protected $renderer;

  /**
   * Constructs a new SystemBreadcrumbBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The Entity Manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountInterface $current_user, RouteMatchInterface $route_match, EntityManagerInterface $entityManager, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentUser = $current_user;
    $this->routeMatch = $route_match;
    $this->entityManager = $entityManager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('current_route_match'),
      $container->get('entity.manager'),
      $container->get('renderer')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function build() {
    $gear = [];
    $world = [];
    $game = [];
    $content = array();

    // We only want people to see this block on their OWN profile page.
    $pageUser = \Drupal::routeMatch()->getParameter('user');
    if ($pageUser instanceof AccountInterface) {
      if ($this->currentUser->id() != $pageUser->id()) {
        return [];
      }
    }

    // Only use node types the user has access to.
    foreach ($this->entityManager->getStorage('node_type')->loadMultiple() as $type) {
      $access = $this->entityManager->getAccessControlHandler('node')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
    }

    foreach ($content as $id => $type) {
      $link = [
        '#type' => 'link',
        '#title' => $type->label(),
        '#url' => Url::fromRoute('node.add', ['node_type' => $id]),
      ];

      switch ($id) {
        case 'inspiration':
        case 'campaign':
        case 'pc':
          $game[$id] = $link;
          break;

        case 'armor':
        case 'augmentation':
        case 'drug':
        case 'gear':
        case 'mind':
        case 'morph':
        case 'robot':
        case 'vehicle':
        case 'weapon':
          $gear[$id] = $link;
          break;

        case 'creature':
        case 'npc':
        case 'faction':
        case 'network':
        case 'location':
        case 'strain':
          $world[$id] = $link;
          break;

        default:
          break;
      }
    }

    $node = \Drupal::routeMatch()->getParameter('node');
    // $node->id();  // get current node id (current url node id)

    $build = [];
    foreach (['game', 'world', 'gear'] as $type) {
      if (count($$type)) {
        $build[$type] = array(
          '#theme' => 'item_list',
          '#items' => $$type,
          '#cache' => ['tags' => $this->entityManager->getDefinition('node_type')->getListCacheTags()],
        );
      }
    }
    return $build;
  }

  public function getCacheTags() {
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      return Cache::mergeTags(parent::getCacheTags(), array('node:' . $node->id()));
    } else {
      return parent::getCacheTags();
    }
  }

  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), array('route', 'user.permissions'));
  }
}
