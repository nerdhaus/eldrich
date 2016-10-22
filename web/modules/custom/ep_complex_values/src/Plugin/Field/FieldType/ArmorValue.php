<?php

namespace Drupal\ep_complex_values\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'stat_block' field type.
 *
 * @FieldType(
 *   id = "armor_value",
 *   label = @Translation("Armor"),
 *   description = @Translation("Eclipse Phase armor"),
 *   category = @Translation("Eclipse Phase"),
 *   default_widget = "armor_widget",
 *   default_formatter = "armor_text" * )
 */
class ArmorValue extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['energy'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Energy protection'))
      ->setRequired(FALSE);
    $properties['kinetic'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Kinetic protection'))
      ->setRequired(FALSE);
    $properties['replaces'] = DataDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Replaces existing armor'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'energy' => ['type' => 'int', 'unsigned' => FALSE, 'not null' => FALSE],
        'kinetic' => ['type' => 'int', 'unsigned' => FALSE, 'not null' => FALSE],
        'replaces' => ['type' => 'int', 'size' => 'tiny', 'default' => 0],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if (
      empty($this->values['kinetic']) &&
      empty($this->values['energy'])
    ) {
      return TRUE;
    }
    return FALSE;
  }
}
