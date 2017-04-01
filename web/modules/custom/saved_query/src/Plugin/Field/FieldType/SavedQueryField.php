<?php

namespace Drupal\saved_query\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'saved_query_field' field type.
 *
 * @FieldType(
 *   id = "saved_query_field",
 *   label = @Translation("Saved Query"),
 *   description = @Translation("Stores information needed to run a saved database query"),
 *   default_widget = "raw_saved_query_widget",
 *   default_formatter = "preview_saved_query_formatter"
 * )
 */
class SavedQueryField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'target_field' => '',
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['entity_type'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Entity Type'))
      ->setRequired(TRUE);
    $properties['conditions'] = DataDefinition::create('varchar')
      ->setLabel(new TranslatableMarkup('Conditions'))
      ->setRequired(TRUE);
    $properties['limit'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Result limit'))
      ->setRequired(FALSE);
    $properties['interval'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Refresh Interval'))
      ->setRequired(FALSE);
    $properties['refreshed'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Last Refresh'))
      ->setRequired(FALSE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'entity_type' => [
          'type' => 'varchar',
          'binary' => FALSE,
        ],
        'conditions' => [
          'type' => 'varchar',
        ],
        'limit' => [
          'type' => 'integer',
        ],
        'interval' => [
          'type' => 'integer',
        ],
        'refreshed' => [
          'type' => 'integer',
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();
    return $constraints;
  }

  /**
   * {@inheritdoc}
   *
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['target_field'] = [
      '#type' => 'number',
      '#title' => t('Target Field'),
      '#default_value' => $this->getSetting('target_field'),
      '#required' => TRUE,
      '#description' => t('The Entity Reference field this query is intended to populate.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $type = $this->get('entity_type')->getValue();
    $conditions = $this->get('conditions')->getValue();
    return empty($type) || empty($conditions);
  }

}
