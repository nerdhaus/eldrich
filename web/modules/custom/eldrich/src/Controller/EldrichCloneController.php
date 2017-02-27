<?php

namespace Drupal\eldrich\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Drupal\Core\Url;
use Drupal\Node\Entity\Node;

/**
 * Defines a generic controller to clone an entity with a new author,
 * then redirect to its edit form.
 */
class EldrichCloneController implements ContainerInjectionInterface {

  /**
   * The entity manager
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;


  /**
   * Creates an EldrichCloneController object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * Clones a node and redirects the user to its edit form.
   *
   * @param EntityInterface $original
   *   The Node to be cloned.
   *
   * @return RedirectResponse
   *   The edit form if successful, the original node page if not.
   */
  public function cloneAndEdit($original) {
    $type_id = $original->bundle();
    if (!\Drupal::currentUser()->hasPermission("create $type_id content")) {
      drupal_set_message(t("You don't have permission to create %type content.", ['%type' => $type_id]), 'error');
      $destination = Url::fromRoute('entity.node.canonical', ['node' => $original->id()]);
    }
    elseif ($clone = $this->constructClone($original)) {
      $destination = Url::fromRoute('entity.node.edit_form', ['node' => $clone->id()]);
    }
    else {
      drupal_set_message(t("The content couldn't be cloned successfully."), 'error');
      $destination = Url::fromRoute('entity.node.canonical', ['node' => $original->id()]);
    }
    return new RedirectResponse($destination->toString());
  }

  /**
   * Handles the cloning process itself.
   *
   * @param EntityInterface $original
   *   The Node to be cloned.
   * @return EntityInterface $clone
   *   The new node, with its assorted properties updated and ready to be saved.
   */
  public function constructClone(EntityInterface $original) {
    $clone = $original->createDuplicate();


    if ($original->bundle() == 'npc' && \Drupal::request()->query->get('target') == 'pc') {
      $clone->type = 'pc';
    }

    foreach ($this->referencesToClone() as $field_name) {
      if ($clone->hasField($field_name) && !$clone->$field_name->isEmpty()) {
        foreach ($clone->$field_name as $delta => $fi) {
          $oldReference = $fi->entity;
          $newReference = $oldReference->createDuplicate();
          $newReference->save();

          // We do this instead of building the field data from scratch to
          // preserve extras in EntityReferenceQuantity and Override fields
          $clone->$field_name[$delta]->target_id = $newReference->id();
        }
      }
    }

    foreach ($this->fieldToClear() as $field_name) {
      if ($clone->hasField($field_name) && !$clone->$field_name->isEmpty()) {
        $clone->$field_name->setValue(null);
      }
    }

    $clone->uid = \Drupal::currentUser()->id();
    if ($clone->hasField('field_based_on')) {
      $clone->field_based_on->setValue(['target_id' => $original->id()]);
    }
    $clone->title->value = 'Clone of ' . $clone->title->value;
    $clone->save();

    return $clone;
  }

  /**
   * @return array
   *   A list of fields referencing entities that should themselves be
   *   cloned.
   */
  private function referencesToClone() {
    $fields = [
      'field_morph',
      'field_identity',
      'field_muse',
      'field_status',
      'field_equipped_armor',
      'field_equipped_weapons',
      'field_native_abilities',
      'field_native_attacks',
    ];
    return $fields;
  }

  /**
   * @return array
   *   A list of fields whose values should be cleared instead of cloned.
   */
  private function fieldToClear() {
    $fields = [
      'field_sources', // Don't clone sources.
      'field_image'
    ];
    return $fields;
  }
}
