<?php

/**
 * @file
 * Definition of Drupal\list_formatter\Tests\TestBase.
 */

namespace Drupal\list_formatter\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test base class for list_formatter tests.
 */
abstract class TestBase extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('list_formatter', 'node', 'field', 'field_ui');

  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser(array('bypass node access', 'administer content types', 'administer nodes'));

    $this->contentType = $this->drupalCreateContentType();

    $this->fieldName = drupal_strtolower($this->randomName() . '_field_name');
    $field = array('field_name' => $this->fieldName, 'type' => 'text', 'cardinality' => -1);
    $this->field = field_create_field($field);

    $this->instance = array(
      'field_name' => $this->fieldName,
      'entity_type' => 'node',
      'bundle' => $this->contentType->type,
      'label' => $this->randomName() . '_label',
      'description' => $this->randomName() . '_description',
      'weight' => mt_rand(0, 127),
      'settings' => array(
        'max_length' => 255,
      ),
    );
    field_create_instance($this->instance);

    $display = array(
      'label' => 'above',
      'module' => 'list_formatter',
      'settings' => array(
        'class' => 'list-formatter-list',
        'comma_and' => 0,
        'comma_full_stop' => 0,
        'comma_override' => 0,
        'comma_tag' => 'div',
        'contrib' => array(),
        'separator_custom' => '',
        'separator_custom_class' => 'list-formatter-separator',
        'separator_custom_tag' => 'span',
        'term_plain' => 0,
        'type' => 'ul',
      ),
      'type' => 'list_formatter',
      'weight' => '10',
    );

    entity_get_display('node', $this->contentType->type, 'full')
      ->setComponent($this->fieldName, $display)
      ->save();
  }

}
