<?php

namespace Drupal\ep_skill_reference\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;

/**
 * Plugin implementation of the 'skill_reference' field type.
 *
 * @FieldType(
 *   id = "skill_reference",
 *   label = @Translation("Skill reference"),
 *   description = @Translation("Reference to an Eclipse Phase skill record"),
 *   category = @Translation("Eclipse Phase"),
 *   default_widget = "skill_reference_select",
 *   default_formatter = "skill_reference_label",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList"
 * )
 */
class SkillReference extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $field = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Field'))
      ->setRequired(FALSE);
    $properties['field'] = $field;

    $points = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Points'))
      ->setRequired(TRUE);
    $properties['points'] = $points;

    $specialization = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Specialization'))
      ->setRequired(FALSE);
    $properties['specialization'] = $specialization;

//    $properties['fieldable'] = DataDefinition::create('boolean')
//      ->setLabel(t('Fieldable'))
//      ->setComputed(TRUE)
//      ->setReadOnly(TRUE)
//      ->setClass('Drupal\ep\SkillFieldChecker');

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    $schema['columns']['field'] = array(
      'type' => 'varchar',
      'length' => 64
    );
    $schema['columns']['specialization'] = array(
      'type' => 'varchar',
      'length' => 64
    );
    $schema['columns']['points'] = array(
      'type' => 'int',
      'not null' => TRUE,
      'default' => 0,
      'unsigned' => FALSE,
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions() {
    // In the base EntityReference class, this is used to populate the
    // list of field-types with options for each destination entity type.
    // Too much work, we'll just make people fill that out later.
    // Also, keeps the field type dropdown from getting too cluttered.
    return array();
  }

}
