<?php
namespace Drupal\operation\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\Plugin\Field\FieldType\IntegerItem;

/**
 * Defines the 'integer' field type.
 *
 * @FieldType(
 *   id = "operation",
 *   label = @Translation("Operation (integer)"),
 *   description = @Translation("This field stores a number and a mathematical operator."),
 *   category = @Translation("Number"),
 *   default_widget = "operation_widget",
 *   default_formatter = "operation_formatter"
 * )
 */
class Operation extends IntegerItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return array(
      'operator' => '',
    ) + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);
    $properties['operator'] = DataDefinition::create('string')
      ->setLabel(t('Operator'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    $schema['columns']['operator'] = array(
      'type' => 'varchar',
      'length' => 1,
    );
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values = parent::generateSampleValue($field_definition);
    $values['operator'] = array_rand(['+', '-', '/', '*', '=', '%', '^']);
    return $values;
  }

}
