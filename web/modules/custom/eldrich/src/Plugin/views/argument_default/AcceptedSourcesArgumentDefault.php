<?php

namespace Drupal\eldrich\Plugin\views\argument_default;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Cache\Cache;

/**
 * Default argument plugin to extract a node.
 *
 * @ViewsArgumentDefault(
 *   id = "accepted_sources",
 *   title = @Translation("Source IDs by user preference")
 * )
 */
class AcceptedSourcesArgumentDefault extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * The Current User object.
   *
   * @var \Drupal\User\Entity\User;
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
    $this->currentUser = User::load($current_user->id());
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
    if (!is_null($this->currentUser) && $this->currentUser->hasField('field_canon_preference')) {
      $preference = $this->currentUser->field_canon_preference->value;
    }
    else {
      $preference = 'canon';
    }

    $player_query = $this->query->andConditionGroup()
      ->condition('type', 'campaign')
      ->condition('field_pcs.entity.uid', $this->currentUser->id());

    $owner_query = $this->query->andConditionGroup()
      ->condition('type', ['campaign', 'inspiration'], 'IN')
      ->condition('uid', $this->currentUser->id());

    $canon_query = $this->query->andConditionGroup()
      ->condition('type', 'source');

    switch ($preference) {
      case 'all':
        // Super permissive. Just let anything through.
        return 'all';

      case 'mine':
        // Show all canon materials, AND materials sourced in campaigns
        // the user plays in, AND materials sourced in campaigns or inspiration
        // the user created.
        $all_conditions = $this->query->orConditionGroup()
          ->condition($canon_query)
          ->condition($owner_query)
          ->condition($player_query);
        break;

      case 'my_games':
        // Show all canon materials, AND materials sourced in campaigns
        // the user plays in.
        $all_conditions = $this->query->orConditionGroup()
          ->condition($canon_query)
          ->condition($player_query);
        break;

      default:
        // Canon materials only.
        // Literally only show canon materials. Everything else hidden.
        $all_conditions = $this->query->orConditionGroup()
          ->condition($canon_query);
        break;
    }

    $nids = $this->query->condition($all_conditions)->execute();
    $value = join(',', $nids);
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0; // Cache::PERMANENT;
  }
}
