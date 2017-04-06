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
use Drupal\Component\Serialization\Json;

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
      ->setLabel(new TranslatableMarkup('Entity Type'));

    $properties['sorts'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Sorts'));

    $properties['conditions'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Conditions'));

    $properties['limit'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Result limit'));

    $properties['interval'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Refresh Interval'));

    $properties['refreshed'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Last Refresh'));

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
          'default' => 'node',
          'length' => 64
        ],
        'conditions' => [
          'type' => 'varchar',
          'length' => 2048
        ],
        'sorts' => [
          'type' => 'varchar',
          'length' => 2048
        ],
        'limit' => ['type' => 'int'],
        'interval' => ['type' => 'int'],
        'refreshed' => ['type' => 'int'],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    // TODO: Ensure conditions and sorts serialize properly
    $constraints = parent::getConstraints();
    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $supported_fields = ['entity_reference', 'entity_reference_quantity', 'entity_reference_override'];
    $entity_type_id = $this->getFieldDefinition()->getTargetEntityTypeId();
    $bundle = $this->getFieldDefinition()->getTargetBundle();

    $options = []; // Get the entity reference fields attached to this bundle

    foreach (\Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle) as $field_name => $field_definition) {
      if (!empty($field_definition->getTargetBundle())) {
        if (in_array($field_definition->getType(), $supported_fields)) {
          $options[$field_name] = $field_definition->getLabel();
        }
      }
    }

    $elements = [];
    $elements['target_field'] = [
      '#type' => 'select',
      '#title' => t('Target Field'),
      '#default_value' => $this->getSetting('target_field'),
      '#options' => $options,
      '#description' => t('The reference field this query is intended to populate.'),
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
    $conditions = $this->conditions;
    if (!is_array($conditions)) {
      $conditions = Json::decode($conditions);
      if (!is_array($conditions)) {
        return [];
      }
    }
    return $conditions;
  }

  public function setConditions(Array $conditions) {
    $this->set('conditions', Json::encode($conditions));
  }

  public function getSorts() {
    $sorts = $this->sorts;
    if (!is_array($sorts)) {
      $sorts = Json::decode($sorts);
      if (!is_array($sorts)) {
        return [];
      }
    }
    return $sorts;
  }

  public function setSorts(Array $sorts) {
    $this->set('sorts', Json::encode($sorts));
  }


  public function preSave() {
    if (is_array($this->conditions)) {
      $this->conditions = Json::encode($this->conditions);
    }
    if (is_array($this->sorts)) {
      $this->sorts = Json::encode($this->sorts);
    }
  }

  /**
   * Returns a ready-to-execute EntityQueryInterface instance.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query object that can query the given entity type.
   */
  public function getQuery() {
    $query = \Drupal::entityQuery($this->entity_type);
    $token = \Drupal::token();

    if ($limit = $this->limit) {
      $query->range(0, $limit);
    }

    foreach ($this->getConditions() as $key => $condition) {
      if (strtolower($key) == 'and') {
        // TODO: AndConditionGroups
      }
      elseif (strtolower($key) == 'or') {
        // TODO: OrConditionGroups
      }
      else {
        if (is_array($condition)) {
          $value = $token->replace($condition['value']);
          $query->condition($key, $condition['value'], $condition['operator'] ?: '=');
        }
        else {
          $value = $token->replace($condition);
          $query->condition($key, $condition);
        }
      }
    }

    foreach ($this->getSorts() as $field => $direction) {
      $query->sort($field, $direction);
    }

    return $query;
  }
}
