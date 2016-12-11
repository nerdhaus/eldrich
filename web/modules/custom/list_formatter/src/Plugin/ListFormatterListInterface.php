<?php

/**
 * @file
 * Contains \Drupal\list_formatter\Plugin\ListFormatterListInterface.
 */

namespace Drupal\list_formatter\Plugin;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterInterface;

interface ListFormatterListInterface {

  /**
   * Creates a list from field items.
   *
   * @param  [type] $langcode
   * @param  [type] $items
   *
   * @return array
   */
  public function createList(FieldItemListInterface $items, FieldDefinitionInterface $field_definition, $langcode);

  /**
   * [additionalSettings description]
   *
   * @param  [type] $form
   * @param  [type] $form_state
   * @param  [type] $context
   */
  public function additionalSettings(&$elements, FieldDefinitionInterface $field_definition, FormatterInterface $formatter);

}
