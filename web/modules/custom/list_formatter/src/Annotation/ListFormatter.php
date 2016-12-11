<?php

namespace Drupal\list_formatter\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a List Formatter plugin.
 *
 * @Annotation
 */
class ListFormatter extends Plugin {

  public $field_types = [];

  public $settings = [];

  public $module;

}
