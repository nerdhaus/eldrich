<?php

namespace Drupal\saved_query;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Saved query entity.
 *
 * @see \Drupal\saved_query\Entity\SavedQuery.
 */
class SavedQueryAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\saved_query\Entity\SavedQueryInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished saved query entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published saved query entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit saved query entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete saved query entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add saved query entities');
  }

}
