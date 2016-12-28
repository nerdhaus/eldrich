<?php

/**
 * @file
 * Contains \Drupal\display_fields\Entity\DisplayFieldsConfig.
 */

namespace Drupal\display_fields\Entity;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Configuration entity that contains display fields configurations for an entity type / bundle.
 *
 * @ConfigEntityType(
 *   id = "display_fields_config",
 *   label = @Translation("Display fields configuration"),
 *   entity_keys = {
 *     "id" = "id",
 *     "status" = "status"
 *   }
 * )
 */
class DisplayFieldsConfig extends ConfigEntityBase implements ConfigEntityInterface {

  protected $display_fields;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->targetEntityType . '.' . $this->bundle;
  }

}
