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
 * Plugin implementation of the 'mobility_speed' field type.
 *
 * @FieldType(
 *   id = "mobility_speed",
 *   label = @Translation("Mobility speed"),
 *   description = @Translation("Stores three key speed values"),
 *   category = @Translation("Eclipse Phase"),
 *   default_widget = "mobility_speed_widget",
 *   default_formatter = "mobility_speed_formatter"
 * )
 */
class MobilitySpeed extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['walk'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Walking'));
    $properties['run'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Running'));
    $properties['cruise'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Cruising'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'walk' => ['type' => 'int', 'unsigned' => TRUE, 'not null' => FALSE],
        'run' => ['type' => 'int', 'unsigned' => TRUE, 'not null' => FALSE],
        'cruise' => ['type' => 'int', 'unsigned' => TRUE, 'not null' => FALSE],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['walk'] = 4;
    $values['run'] = 20;
    $values['cruise'] = 20;
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if (
      empty($this->values['walk']) &&
      empty($this->values['run']) &&
      empty($this->values['cruise'])
    ) {
      return TRUE;
    }
    return FALSE;
  }
}
