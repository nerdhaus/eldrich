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
 *   title = @Translation("Content IDs from allowed sources")
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

    $owned_or_open = $this->query->orConditionGroup()
      ->condition('uid', $this->currentUser->id())
      ->condition('field_allow_others.value', TRUE);
    $inspiration_query = $this->query->andConditionGroup()
      ->condition($owned_or_open)
      ->condition('type', 'inspiration');

    $player_or_owner = $this->query->orConditionGroup()
      ->condition('field_pcs.entity.uid', $this->currentUser->id())
      ->condition('uid', $this->currentUser->id());
    $campaign_query = $this->query->andConditionGroup()
      ->condition($player_or_owner)
      ->condition('type', 'campaign');

    if (in_array('contributor', $roles)) {
      $sources_query = $this->query
        ->condition('type', 'source');

      $all_conditions = $this->query->orConditionGroup()
        ->condition($inspiration_query)
        ->condition($campaign_query)
        ->condition($sources_query);
    }
    else {
      $all_conditions = $this->query->orConditionGroup()
        ->condition($inspiration_query)
        ->condition($campaign_query);
    }

    $nids = $this->query->condition($all_conditions)->execute();
    $value = join(',', $nids);

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
