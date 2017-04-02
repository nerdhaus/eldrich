<?php

namespace Drupal\saved_query\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Component\Serialization\Yaml;

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
    // Ensure constraints are valid yml
    $constraints = parent::getConstraints();
    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $options = []; // Get the entity reference fields attached to this bundle

    $elements['target_field'] = [
      '#type' => 'select',
      '#title' => t('Target Field'),
      '#default_value' => $this->getSetting('target_field'),
      '#options' => $options,
      '#description' => t('The Entity Reference field this query is intended to populate.'),
      '#required' => TRUE,
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

  /**
   * Returns an (optionally nested) array of query criteria.
   *
   * @return array
   *   These are not actually core QueryCondition objects, just the data used
   *   to create them.
   */
  public function getConditions() {
    $raw_conditions = $this->get('conditions');
    $conditions = Yaml::decode($raw_conditions);
    if (!is_array($conditions)) {
      return [];
    }
    return $conditions;
  }

  /**
   * Sets the conditions for the saved query from a YAML structure.
   *
   * @param array $conditions
   *   An (optionally nested) array of query conditions.
   */
  public function setConditions($conditions) {
    $this->set('conditions', Yaml::encode($conditions));
  }

  /**
   * Returns a ready-to-execute EntityQueryInterface instance.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query object that can query the given entity type.
   */
  public function getQuery() {
    $query = \Drupal::entityQuery($this->get('entity_type'));
    if ($limit = $this->get('limit')) {
      $query->pager(['limit' => $limit]);
    }

    foreach ($this->getConditions() as $key => $condition) {
      if ($key == 'and') {
        // TODO: AndConditionGroups
      }
      elseif ($key == 'or') {
        // TODO: OrConditionGroups
      }
      else {
        $query->condition($condition['field'], $condition['value'], $condition['operator'] ?: '=');
      }
    }

    return $query;
  }
}
