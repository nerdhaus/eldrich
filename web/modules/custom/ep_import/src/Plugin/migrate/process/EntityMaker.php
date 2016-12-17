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
 *   id = "entity_maker",
 *   handle_multiples = FALSE
 * )
 */
class EntityMaker extends ProcessPluginBase implements ContainerFactoryPluginInterface {

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
     * Runs a process pipeline on each destination property per list item.
     */
    public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
        $raw = array_combine($this->configuration['keys'], $value);
        $entity_type = $this->configuration['entity_type'];

        $entity_values = array();

        if (isset($this->configuration['default_values']) && is_array($this->configuration['default_values'])) {
            foreach ($this->configuration['default_values'] as $key => $value) {
                $entity_values[$key] = $value;
            }
        }

        $new_row = new Row($raw, array());
        $migrate_executable->processRow($new_row, $this->configuration['process']);
        $destination = $new_row->getDestination();
        foreach ($destination as $key => $value) {
            $entity_values[$key] = $value;
        }

        $entity = $this->entityManager
            ->getStorage($this->configuration['entity_type'])
            ->create($entity_values);

        $entity->save();
        $entity_values['id'] = $entity->id();
        return $entity->id();
    }

    /**
     * {@inheritdoc}
     */
    public function multiple() {
        return FALSE;
    }

}
