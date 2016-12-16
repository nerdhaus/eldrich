<?php

namespace Drupal\ep_import\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\ProcessPluginBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ep_import\Plugin\migrate\process\MorphInstance;


/**
 * This plugin generates ArmorInstance entities.
 *
 * @MigrateProcessPlugin(
 *   id = "muse_instance"
 * )
 */
class MuseInstance extends MorphInstance {
  public function constructInstance($entity_values) {
    // We're so lazy we're going to load the standard muse. Just slap those values in.
    // Later we'll make it possible to pre-populate the muse.

    $query = \Drupal::entityQuery('node');
    $query->condition('title', 'Standard Muse');
    $query->condition('type', 'muse');
    $results = $query->execute();

    $muse = $this->entityManager->getStorage('node')->load($id);

    $entity_values = [
      'type' => 'muse',
      'title' => 'Standard Muse',
      'field_stats' => $mind->field_stats->getValue(),
      'field_skills' => $mind->field_skills->getValue(),
    ];

    return $entity_values;
  }
}
