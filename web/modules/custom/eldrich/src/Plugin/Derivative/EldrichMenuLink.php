<?php

namespace Drupal\eldrich\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\taxonomy\TermStorageInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides custom dynamic menu links for Eldrich Host.
 *
 * @see \Drupal\eldrich\Plugin\Menu\EldrichMenuLink
 */
class EldrichMenuLink extends DeriverBase implements ContainerDeriverInterface {

  private $basePluginDefinition;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The taxonomy term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * @param \Drupal\node\NodeStorageInterface $nodeStorage
   *   The node storage.
   * @param \Drupal\taxonomy\TermStorageInterface $termStorage
   *   The term storage.
   * @param \Drupal\Core\Entity\Query\QueryFactory $queryFactory
   *   Query Factory For Queryin'.
   */
  public function __construct(NodeStorageInterface $nodeStorage, TermStorageInterface $termStorage, QueryFactory $queryFactory) {
    $this->nodeStorage = $nodeStorage;
    $this->termStorage = $termStorage;
    $this->queryFactory = $queryFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity.manager')->getStorage('node'),
      $container->get('entity.manager')->getStorage('taxonomy_term'),
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->basePluginDefinition = $base_plugin_definition;

    $links = array();

    $cids = $this->nodeStorage->getQuery()
      ->condition('type', 'campaign')
      ->condition('status', NODE_PUBLISHED)
      ->execute();

    if (count($cids)) {
      $links[] = [
        'id' => 'eldrich.dynamic.separator',
        'route_name' => '<nolink>',
        'title' => '-',
        'parent' => 'views_view:views.games.overview',
        'weight' => 100,
        'menu_name' => 'main'
      ];
      $campaigns = $this->nodeStorage->loadMultiple($cids);
      foreach ($campaigns as $campaign) {
        $links[] = $this->createNodeDerivativeDefinition($campaign, 200);
      }
    }

    $tids = $this->termStorage->getQuery()
      ->condition('vid', 'gear_type')
      ->condition('field_featured', TRUE)
      ->execute();

    if (count($tids)) {
      $terms = $this->termStorage->loadMultiple($tids);
      foreach ($terms as $term) {
        $links[] = $this->createTermDerivativeDefinition($term, -1);
      }
    }

    return $links;
  }

  /**
   * @param EntityInterface $term
   * @param int $weight
   * @return array
   */
  private function createTermDerivativeDefinition(EntityInterface $term, $weight = 0) {
    $title = $term->field_short_name->value ?: $term->label();
    $derivative = [
        'route_name' => 'entity.taxonomy_term.canonical',
        'route_parameters' => ['taxonomy_term' => $term->id()],
        'title' => $title,
        'parent' => 'views_view:views.taxonomy_term.catalog',
        'weight' => $weight,
        'menu_name' => 'main'
      ] + $this->basePluginDefinition;
    return $derivative;
  }

  /**
   * @param EntityInterface $node
   * @param int $weight
   * @return array
   */
  private function createNodeDerivativeDefinition(EntityInterface $node, $weight = 0) {
    $derivative = [
        'route_name' => 'entity.node.canonical',
        'route_parameters' => ['node' => $node->id()],
        'title' => $node->label(),
        'parent' => 'views_view:views.games.overview',
        'weight' => $weight,
        'menu_name' => 'main'
      ] + $this->basePluginDefinition;
    return $derivative;
  }
}
