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
 * This plugin implements a sub-import for individual morphs.
 *
 * @MigrateProcessPlugin(
 *   id = "standard_muse",
 *   handle_multiples = FALSE
 * )
 */
class StandardMuse extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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

  /**
   * Just takes in the name of a muse and pops in the standard muse stats. Laaaaaazy.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $id = $this->entityManager->getStorage('node')
      ->getQuery()->condition('title', 'Standard Muse')->execute();
    $standard_muse = \Drupal::entityTypeManager()->getStorage('node')->load(reset($id));

    $new_muse = [
      'title' => trim($value),
      'type' => 'muse',
      'field_description' => $standard_muse->field_description->getValue(),
      'field_skills' => $standard_muse->field_skills->getValue(),
      'field_stats' => $standard_muse->field_stats->getValue(),
    ];

    $entity = $this->entityManager->getStorage('instance')->create($new_muse);
    $entity->save();
    return $entity->id();
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return FALSE;
  }
}
