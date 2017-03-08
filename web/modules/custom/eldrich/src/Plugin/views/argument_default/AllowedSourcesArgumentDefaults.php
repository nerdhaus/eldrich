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

    $player_owned_open = $this->query->orConditionGroup()
      ->condition('field_allow_others.value', TRUE)
      ->condition('field_pcs.entity.uid', $this->currentUser->id())
      ->condition('uid', $this->currentUser->id());

    $nids = \Drupal::entityQuery('node')
      ->condition('type', ['campaign', 'inspiration'], 'IN')
      ->condition($player_owned_open)
      ->execute();


    if (in_array('contributor', $roles)) {
      $books = \Drupal::entityQuery('node')
        ->condition('type', 'source')
        ->execute();
      $nids = array_merge($nids, $books);
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
