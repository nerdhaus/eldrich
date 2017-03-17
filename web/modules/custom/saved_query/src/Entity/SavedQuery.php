<?php

namespace Drupal\saved_query\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Component\Serialization\Yaml;

/**
 * Defines the Saved query entity.
 *
 * @ingroup saved_query
 *
 * @ContentEntityType(
 *   id = "saved_query",
 *   label = @Translation("Saved query"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\saved_query\SavedQueryListBuilder",
 *     "views_data" = "Drupal\saved_query\Entity\SavedQueryViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\saved_query\Form\SavedQueryForm",
 *       "add" = "Drupal\saved_query\Form\SavedQueryForm",
 *       "edit" = "Drupal\saved_query\Form\SavedQueryForm",
 *       "delete" = "Drupal\saved_query\Form\SavedQueryDeleteForm",
 *     },
 *     "access" = "Drupal\saved_query\SavedQueryAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\saved_query\SavedQueryHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "saved_query",
 *   admin_permission = "administer saved query entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/saved_query/{saved_query}",
 *     "add-form" = "/admin/structure/saved_query/add",
 *     "edit-form" = "/admin/structure/saved_query/{saved_query}/edit",
 *     "delete-form" = "/admin/structure/saved_query/{saved_query}/delete",
 *     "collection" = "/admin/structure/saved_query",
 *   },
 *   field_ui_base_route = "saved_query.settings"
 * )
 */
class SavedQuery extends ContentEntityBase implements SavedQueryInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * Sets entity type a query should apply to.
   *
   * @param string $entityTypeId
   *   ID of the entity type to be used.
   *
   * @return \Drupal\saved_query\Entity\SavedQueryInterface
   *   The called Saved query entity.
   */
  public function setEntityType($entityTypeId) {
    $this->set('entity_type', $entityTypeId);
    return $this;
  }

  /**
   * Returns an (optionally nested) array of query criteria.
   *
   * @return array
   *   These are not actually core QueryCondition objects, just the data used
   *   to create them.
   */
  public function getConditions() {
    $raw_conditions = $this->get('conditions')->value;
    $conditions = Yaml::decode($raw_conditions);
    if (!is_array($conditions)) {
      return [];
    }
    return $conditions;
  }

  /**
   * Sets the published status of a Saved query.
   *
   * @param array $conditions
   *   An (optionally nested) array of query conditions.
   *
   * @return \Drupal\saved_query\Entity\SavedQueryInterface
   *   The called Saved query entity.
   */
  public function setConditions($conditions) {
    $this->set('conditions', Yaml::encode($conditions));
    return $this;
  }

  /**
   * Returns the Saved query published status indicator.
   *
   * @return int
   *   The number of records to be returned. -1 implies no limit.
   */
  public function getLimit() {
    return $this->get('limit')->value;
  }

  /**
   * Sets the published status of a Saved query.
   *
   * @param int $limit
   *   The number of records that should be returned.
   *
   * @return \Drupal\saved_query\Entity\SavedQueryInterface
   *   The called Saved query entity.
   */
  public function setLimit($limit) {
    $this->set('limit', $limit);
    return $this;
  }

  /**
   * Returns a ready-to-execute EntityQueryInterface instance.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query object that can query the given entity type.
   */
  public function getQuery() {
    $query = \Drupal::entityQuery($this->getEntityTypeId());
    if ($limit = $this->getLimit()) {
      $query->pager(['limit' => $limit]);
    }

    foreach ($this->getConditions() as $key => $condition) {
      $query->condition($condition['field'], $condition['value'], $condition['operator'] ?: '=');
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Saved query entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the saved query.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('');

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity Type'))
      ->setDescription(t('The entity type to be queried.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('node');

    $fields['limit'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Query Limit'))
      ->setDescription(t('The maximum number of rows to return.'));

    $fields['conditions'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Query Limit'))
      ->setDescription(t('The maximum number of rows to return.'));

    return $fields;
  }
}
