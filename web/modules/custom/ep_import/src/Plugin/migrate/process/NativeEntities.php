<?php

namespace Drupal\ep_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This plugin just grabs campaign ids.
 *
 * @MigrateProcessPlugin(
 *   id = "native_entities",
 *   handle_multiples = FALSE
 * )
 */
class NativeEntities extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /** @var \Drupal\Core\Entity\EntityManagerInterface */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityManagerInterface $entityManager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityManager = $entityManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity.manager')
    );
  }

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $match_field = $this->configuration['field'];
    $entity_type = $this->configuration['type'];
    $query = $this->entityManager->getStorage('component')->getQuery();
    $ids = $query
      ->condition('type', $entity_type)
      ->condition($match_field, trim($value))
      ->execute();

    $results = [];
    foreach ($ids as $id) {
      $results[]['target_id'] = $id;
    }
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return FALSE;
  }
}
