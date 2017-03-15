<?php

namespace Drupal\saved_query\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Saved query entities.
 *
 * @ingroup saved_query
 */
interface SavedQueryInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Saved query name.
   *
   * @return string
   *   Name of the Saved query.
   */
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
   * Gets the Saved query creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Saved query.
   */
  public function getCreatedTime();

  /**
   * Sets the Saved query creation timestamp.
   *
   * @param int $timestamp
   *   The Saved query creation timestamp.
   *
   * @return \Drupal\saved_query\Entity\SavedQueryInterface
   *   The called Saved query entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Saved query published status indicator.
   *
   * Unpublished Saved query are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Saved query is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Saved query.
   *
   * @param bool $published
   *   TRUE to set this Saved query to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\saved_query\Entity\SavedQueryInterface
   *   The called Saved query entity.
   */
  public function setPublished($published);

}
