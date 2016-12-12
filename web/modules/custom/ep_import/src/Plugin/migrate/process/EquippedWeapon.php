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
 * This plugin generates WeaponInstance entities.
 *
 * @MigrateProcessPlugin(
 *   id = "equipped_weapon"
 * )
 */
class EquippedWeapon extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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

    $entities = [];

    $weapons = $this::explim(';', $value);
    foreach ($weapons as $weapon) {
      list($weapon, $ammo) = $this::explim('+', $weapon);
      preg_match("/(?<gear>[^{]*)?(?:{(?<mods>.*)})?/", $weapon, $weapon);
      preg_match("/(?<gear>[^{]*)?(?:{(?<mods>.*)})?/", $ammo, $ammo);

      $gear = [
        'weapon' => [
          'base' => $weapon['gear'],
          'mods' => $this::explim(',', $weapon['mods']),
        ],
        'ammo' => [
          'base' => $ammo['gear'],
          'mods' => $this::explim(',', $ammo['mods']),
        ],
      ];

      $entities[] = $this::constructInstance($gear);
    }

    return $entities;
  }

  public function constructInstance($values) {
    if (empty($values['weapon']['base'])) {
      return NULL;
    }

    $entity_values = [
      'type' => 'weapon_instance',
      'field_weapon' => $this::getGearIDs($values['weapon']['base']),
      'field_weapon_mods' => $this::getGearIDs($values['weapon']['mods']),
      'field_ammo' => $this::getGearIDs($values['ammo']['base']),
      'field_ammo_mods' => $this::getGearIDs($values['ammo']['mods']),
    ];

    if (empty($entity_values['field_weapon'])) {
      return NULL;
    }

    $entity = $this->entityManager
      ->getStorage('instance')
      ->create($entity_values);
    $entity->save();

    return $entity->id();
  }

  public function explim($delimiter, $string) {
    $values = explode($delimiter, $string);
    foreach ($values as $key => $value) {
      $values[$key] = trim($value);
    }
    return $values;
  }

  public function getGearIDs($value) {
    if (empty($value)) {
      return NULL;
    }

    $multiple = is_array($value);

    $query = \Drupal::entityQuery('node');
    $group = $query->orConditionGroup()
      ->condition('title', $value, $multiple ? 'IN' : NULL)
      ->condition('field_short_name.value', $value, $multiple ? 'IN' : NULL);
    $query
      ->condition('type', 'weapon')
      ->condition($group);

    $results = $query->execute();

    if (empty($results)) {
      return NULL;
    }
    else {
      foreach ($results as $key => $result) {
        $results[$key] = ['target_id' => $result];
      }
      return $results;
    }
  }
}
