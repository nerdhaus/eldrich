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
    if ($this->currentUser->isAuthenticated()) {
      $preference = $this->currentUser->get('field_canon_preferences')->value;
    }
    else {
      $preference = 'canon';
    }
    $uid = $this->currentUser->id();

    switch ($preference) {
      case 'all':
        // Super permissive. Just let anything through.
        return 'all';

      case 'mine':
        // Show all canon materials, AND materials sourced in campaigns
        // the user plays in, AND materials sourced in campaigns or inspiration
        // the user created.
        $member_sources = \Drupal::entityQuery('node')
          ->condition('type', 'campaign')
          ->condition('field_pcs.entity.uid', $uid)
          ->execute();

        $owned_sources = \Drupal::entityQuery('node')
          ->condition('type', ['campaign', 'inspiration'], 'IN')
          ->condition('uid', $uid)
          ->execute();

        $canon_nids = \Drupal::entityQuery('node')
          ->condition('type', 'source')
          ->execute();

        $nids = array_merge($member_sources, $owned_sources, $canon_nids);
        break;

      case 'my_games':
        // Show all canon materials, AND materials sourced in campaigns
        // the user plays in.
        $member_sources = \Drupal::entityQuery('node')
          ->condition('type', 'campaign')
          ->condition('field_pcs.entity.uid', $uid)
          ->execute();

        $canon_nids = \Drupal::entityQuery('node')
          ->condition('type', 'source')
          ->execute();

        $nids = array_merge($member_sources, $canon_nids);
        break;

      default:
        // Canon materials only.
        // Literally only show canon materials. Everything else hidden.
        $nids = \Drupal::entityQuery('node')
          ->condition('type', 'source')
          ->execute();
        break;
    }

    $value = join('+', $nids);
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
