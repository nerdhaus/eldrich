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
    if (empty($value)) {
      return NULL;
    }
    if (!is_array($value)) {
      $value = ['morph' => 'value'];
    }
    else {
      $value = array_combine($this->configuration['keys'], $value);
    }

    return $this::constructInstance($value);
  }

  public function constructInstance($value) {
    $morph = $this::getMorphData($value['morph']);
    if (empty($morph)) {
      return NULL;
    }

    $values = [
      'type' => 'morph_instance',
      'field_model' => $morph->id(),
      'field_mobility_system' => $morph->field_mobility_system,
      'field_movement_speed' => $morph->field_mobility_system,
      'field_skills' => $morph->field_skills,
      'field_augmentations' => empty($value['augmentations']) ? $morph->field_augmentations : $value['augmentations'],
      'field_traits' => empty($value['traits']) ? $morph->field_traits : $value['traits'],
    ];

    drush_print_r($values);

    $entity = $this->entityManager
      ->getStorage('instance')
      ->create($values);
    $entity->save();

    return $entity->id();
  }

  public function getMorphData($value) {
    if (empty($value)) {
      return NULL;
    }

    $query = \Drupal::entityQuery('node');
    $group = $query->orConditionGroup()
      ->condition('title', $value)
      ->condition('field_short_name.value', $value);
    $query
      ->condition('type', 'morph')
      ->condition($group);

    $results = $query->execute();

    if (empty($results)) {
      return NULL;
    }

    $node_storage = $this->entityManager->getStorage('node');
    if ($morph = $node_storage->load($results)) {
      return $morph;
    }
    return NULL;
  }
}