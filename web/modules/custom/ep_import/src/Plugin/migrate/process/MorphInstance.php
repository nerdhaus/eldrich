<?php

namespace Drupal\ep_import\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * This plugin generates ArmorInstance entities.
 *
 * @MigrateProcessPlugin(
 *   id = "morph_instance"
 * )
 */
class MorphInstance extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrateExecutable, Row $row, $destinationProperty) {
    $raw = array_combine($this->configuration['keys'], $value);
    $entity_values = $this::constructInstance($raw);

    if (empty($entity_values)) {
      return NULL;
    }

    $entity = $this->entityManager
      ->getStorage('instance')
      ->create($entity_values);
    $entity->save();

    return $entity->id();
  }

  public function constructInstance($entity_values) {
    if (empty($entity_values['field_model']['target_id'])) {
      return NULL;
    }

    $morph = $this->entityManager->getStorage('node')->load($entity_values['field_model']['target_id']);
    if (empty($morph)) {
      return NULL;
    }

    $entity_values['type'] = 'morph_instance';

    foreach (['mobility_system', 'movement_speed', 'skills', 'augmentations', 'traits'] as $field) {
      $key = 'field_' . $field;
      if (empty($entity_values[$key])) {
        $entity_values[$key] = $morph->{$key}->getValue();
      }
    }
    $entity_values['title'] = $morph->label();

    return $entity_values;
  }
}
