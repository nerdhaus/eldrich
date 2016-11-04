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
 * Plugin implementation of the 'weapon_range' field type.
 *
 * @FieldType(
 *   id = "weapon_range",
 *   label = @Translation("Weapon range"),
 *   description = @Translation("Various ranges of weapons, measured in meters"),
 *   category = @Translation("Eclipse Phase"),
 *   default_widget = "weapon_range_widget",
 *   default_formatter = "weapon_range_formatter"
 * )
 */
class WeaponRange extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['short'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Short'));
    $properties['medium'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Medium'));
    $properties['long'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Long'));
    $properties['extreme'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Extreme'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'short' => ['type' => 'int'],
        'medium' => ['type' => 'int'],
        'long' => ['type' => 'int'],
        'extreme' => ['type' => 'int'],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['short'] = random_int(10,100);
    $values['medium'] = $values['short'] * random_int(2,3);
    $values['long'] = $values['long'] * 2;
    $values['extreme'] = $values['long'] * 2;
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if (
      empty($this->values['short']) &&
      empty($this->values['medium']) &&
      empty($this->values['long']) &&
      empty($this->values['extreme'])
    ) {
      return TRUE;
    }
    return FALSE;
  }
}
