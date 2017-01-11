<?php

namespace Drupal\ep_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\ep_statblock\Plugin\migrate\process\StatBlockParser;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * An entirely regrettable affair.
 *
 * Skills in EP are a combination of basic ego/mind abilities, morph/body ability
 * boosts, and training points invested in specific learned skills. Bonuses can
 * modify that but we'll pretend it's just that simple for now.
 *
 * Unfortunately, skills are listed differently in various situations:
 *
 * - NPCs, creatures, and robots list final "total" skill values
 * - Pregen characters break the morph bonus out separately
 * - Actual charsheets track the Ego abilities, skill points spent, and morph
 *   based bonuses separately. (It's important, because it costs double the points
 *   to train above 80 in any skill, before any morph bonus)
 *
 * Hell, NPCs don't even split out the morph and ego ability points when they
 * show statblocks — Eldrich's data is best-guess based on the factory specs for
 * the morphs each NPC uses.
 *
 * This plugin is a monstrous solution to that thorny problem. It's used as a
 * post-process filter on every NPC and PC record's skill points field. It looks
 * at the ego and morph stats coming in from the rest of the import, and
 * subtracts those values from the skills before they're saved.
 *
 * For records originally sourced as NPCs, it subtracts both ego and morph values.
 * For those originally sourced as pregens, it subtracts just the ego values.
 * Elrdrich handles dynamically totaling them later.
 *
 * Jesus wept.
 *
 * @MigrateProcessPlugin(
 *   id = "balance_skills"
 * )
 */
class BalanceSkills extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /** @var \Drupal\Core\Entity\EntityManagerInterface */
  protected $entityManager;

  protected static $mapData;

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
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $ego_stats = $this->extract($row->getSourceProperty('ego_stats'), $migrate_executable, $row, $destination_property);
    $morph_stats = $this->extract($row->getSourceProperty('morph_stats'), $migrate_executable, $row, $destination_property);

    if ($row->hasSourceProperty('ego_stats')) {
      $morph = boolval($row->getSourceProperty('ego_stats'));
    }
    else {
      $morph = FALSE;
    }

    drush_print_r($value);

    $value['points'] = $value['points'] - $ego_stats[$this->map($value['target_id'])];
    if ($morph) {
      $value['points'] = $value['points'] - $morph_stats[$this->map($value['target_id'])];
    }

    drush_print_r($value);

    return $value;
  }

  private function map($id) {
    if (empty(static::$mapData)) {

      $nids = \Drupal::entityQuery('node')
        ->condition('type', 'skill')
        ->execute();

      $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

      foreach ($nodes as $skill) {
        static::$mapData[$skill->id()] = $skill->field_linked_aptitude->entity->field_lookup_code->value;
      }
    }
    return static::$mapData[$id];
  }

  private function extract($value) {
    $value = strtolower(str_replace(' ', '', $value));
    $raw = explode(',', $value);
    $results = [];
    foreach ($raw as $pair) {
      $kv = explode(':', $pair);
      $results[$kv[0]] = $kv[1];
    }
    return $results;
  }
}
