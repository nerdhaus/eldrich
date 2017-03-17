<?php

namespace Drupal\saved_query\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\core\Entity\EntityType;


/**
 * Provides an interface for defining Saved query entities.
 *
 * @ingroup saved_query
 */
interface SavedQueryInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  public function getName();

  /**
   * Sets the Saved query name.
   *
   * @param string $name
   *   The Saved query name.
   *
   * @return \Drupal\saved_query\Entity\SavedQueryInterface
   *   The called Saved query entity.
   */
  public function setName($name);

  /**
   * Gets the saved query's target entity type.
   *
   * @return EntityType
   *   Name of the saved entity type.
   */
  public function getEntityType();

  /**
   * Sets entity type a query should apply to.
   *
   * @param string $entityTypeId
   *   ID of the entity type to be used.
   *
   * @return \Drupal\saved_query\Entity\SavedQueryInterface
   *   The called Saved query entity.
   */
  public function setEntityType($entityTypeId);

  /**
   * Returns an (optionally nested) array of query criteria.
   *
   * @return array
   *   These are not actually core QueryCondition objects, just the data used
   *   to create them.
   */
  public function getConditions();

  /**
   * Sets the published status of a Saved query.
   *
   * @param array $conditions
   *   An (optionally nested) array of query conditions.
   *
   * @return \Drupal\saved_query\Entity\SavedQueryInterface
   *   The called Saved query entity.
   */
  public function setConditions($conditions);

  /**
   * Returns the Saved query published status indicator.
   *
   * @return int
   *   The number of records to be returned. -1 implies no limit.
   */
  public function getLimit();

  /**
   * Sets the published status of a Saved query.
   *
   * @param int $limit
   *   The number of records that should be returned.
   *
   * @return \Drupal\saved_query\Entity\SavedQueryInterface
   *   The called Saved query entity.
   */
  public function setLimit($limit);


  /**
   * Returns a ready-to-execute EntityQueryInterface instance.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query object that can query the given entity type.
   */
  public function getQuery();
}
