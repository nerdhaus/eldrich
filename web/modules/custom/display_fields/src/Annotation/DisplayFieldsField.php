<?php

/**
 * @file
 * Contains Drupal\ds\Annotation\DisplayFieldsField.
 */

namespace Drupal\display_fields\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a DisplayFieldsField annotation object.
 *
 * @Annotation
 */
class DisplayFieldsField extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the Display fields plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $title;

  /**
   * The entity types this plugin should work on.
   *
   * @var array
   */
  public $entity_types;

}
