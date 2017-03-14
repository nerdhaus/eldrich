<?php

namespace Drupal\eldrich\Plugin\views\argument_default;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Default argument plugin to extract a node.
 *
 * @ViewsArgumentDefault(
 *   id = "allowed_sources",
 *   title = @Translation("Source IDs accessible to current user")
 * )
 */
class AllowedSourcesArgumentDefaults extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * The Current User object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The query object.
   *
   * $var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $query;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountInterface $current_user, QueryInterface $query) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->query = $query;
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
      $container->get('entity.query')->get('node')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    $roles = $this->currentUser->getRoles(TRUE);
    if (in_array('administrator', $roles)) {
      return 'all';
    }

    $inspiration = \Drupal::entityQuery('node')
      ->condition('type', 'inspiration');
    $conditions = $inspiration->orConditionGroup()
      ->condition('field_allow_others.value', TRUE)
      ->condition('uid', $this->currentUser->id());
    $inspiration_nids = $inspiration->condition($conditions)->execute();


    $campaigns = \Drupal::entityQuery('node')
      ->condition('type', 'campaign');
    $conditions = $campaigns->orConditionGroup()
      ->condition('field_pcs.entity.uid', $this->currentUser->id())
      ->condition('uid', $this->currentUser->id());
    $campaign_nids = $campaigns->condition($conditions)->execute();

    $nids = array_merge($campaign_nids, $inspiration_nids);

    if (in_array('contributor', $roles)) {
      $book_nids = \Drupal::entityQuery('node')
        ->condition('type', 'source')
        ->execute();
      $nids = array_merge($nids, $book_nids);
    }

    $value = join('+', $nids);

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user.roles'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }
}
