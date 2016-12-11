<?php

/**
 * @file
 * Definition of Drupal\list_formatter\Tests\UITest.
 */

namespace Drupal\list_formatter\Tests;

/**
 * Test the UI settings form for list fields.
 */
class UITest extends TestBase {

  public static function getInfo() {
    return array(
      'name' => 'Test list UI',
      'description' => 'Tests the  settings in the UI for list formatters.',
      'group' => 'List formatter',
    );
  }

  /**
   * Test the general output of the display formatter.
   */
  public function testUI() {
    $this->drupalLogin($this->adminUser);

    $this->drupalGet('admin/structure/types/manage/' . $this->contentType->type . '/display');
    $this->assertResponse(200);

    $this->assertText('Unordered HTML list (ul)');
    $this->assertText('CSS Class: list-formatter-list');
  }

}
