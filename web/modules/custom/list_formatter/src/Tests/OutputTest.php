<?php

/**
 * @file
 * Contains \Drupal\list_formatter\Tests\OutputTest.
 */

namespace Drupal\list_formatter\Tests;

use Drupal\Core\Language\Language;

/**
 * Test the rendered output of list fields.
 */
class OutputTest extends TestBase {

  public static function getInfo() {
    return array(
      'name' => 'Test list output',
      'description' => 'Tests the output markup of list_formatter list formatters.',
      'group' => 'List formatter',
    );
  }

  /**
   * Test the general output of the display formatter.
   */
  public function testOutput() {
    $this->drupalLogin($this->adminUser);

    $field_values = array(Language::LANGCODE_NOT_SPECIFIED => array());
    for ($i = 0; $i < 10; $i++) {
      $field_values[Language::LANGCODE_NOT_SPECIFIED][] = array('value' => $this->randomName());
    }

    $node = $this->drupalCreateNode(array($this->fieldName => $field_values, 'type' => $this->contentType->type));
    $page = $this->drupalGet('node/' . $node->nid);

    $this->drupalSetContent($page);
    $this->assertResponse(200);

    foreach ($field_values[Language::LANGCODE_NOT_SPECIFIED] as $delta => $item) {
      $this->assertText($item['value'], t('Field value !delta output on node.', array('!delta' => $delta)));
    }

    $items = array();
    foreach ($field_values[Language::LANGCODE_NOT_SPECIFIED] as $item) {
      $items[] = $item['value'];
    }

    // Test the default ul list.
    $options = array(
      'type' => 'ul',
      'items' => $items,
      'attributes' => array(
        'class' => array('list-formatter-list'),
      ),
    );
    $expected = theme('item_list', $options);

    $this->assertRaw($expected, 'The expected unordered list markup was produced.');

    // Update the field settings for ol list.
    $display = entity_get_display('node', $this->contentType->type, 'default');
    $field = $display->getComponent($this->fieldName);
    $field['settings']['type'] = 'ol';
    $display->setComponent($this->fieldName, $field)->save();

    // Get the node page again.
    $this->drupalGet('node/' . $node->nid);

    // Test the default ol list.
    $options['type'] = 'ol';
    $expected = theme('item_list', $options);

    $this->assertRaw($expected, 'The expected ordered list markup was produced.');

    // Update the field settings for comma list.
    $field = $display->getComponent($this->fieldName);
    $field['settings']['type'] = 'comma';
    $display->setComponent($this->fieldName, $field)->save();

    // Get the node page again.
    $this->drupalGet('node/' . $node->nid);

    // Test the default comma list.
    unset($options['type']);
    // Get the field formatter plugin to pass into the theme function.
    $options['formatter'] = entity_get_display('node', $this->contentType->type, 'default')->getFormatter($this->fieldName);

    $expected = theme('list_formatter_comma', $options);

    $this->assertRaw($expected, 'The expected comma list markup was produced.');
  }

}
