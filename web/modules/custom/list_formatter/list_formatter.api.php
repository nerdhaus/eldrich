<?php

/**
 * @file
 * Describes hooks provided by the list_formatter module.
 */

/**
 * hook_list_formatter_field_list_info().
 *
 * Declare new field types/callbacks that are available as text formatter lists.
 */
function hook_list_formatter_field_info() {
  $info = array();

  // key array with module name.
  $info['example'] = array(
    // An array of fields that list_formatter can be used on. Typically you
    // would add the name(s) of the fields you are integrating here.
    'fields' => array('example', 'example_other'),
    // Callback to process $items from hook_field_formatter_view, and format
    // an array of values that will be used in the displayed list.
    'callback' => 'list_formatter_example_field_create_list',
    // An array of settings/default settings that will be used. Anything
    // defined in here will be merged into the list_formatter_contrib array in
    // The field formatter info. If your settings are not declared here, no
    // defaults wil exist.
    'settings' => array(
      'list_formatter_example_setting' => TRUE,
    ),
  );

  return $info;
}

/**
 * Sample callback implementation.
 * @see list_formatter_default_field_create_list()
 */
function list_formatter_example_field_create_list($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  $list_items = array();

  foreach ($items as $delta => $item) {
    $list_items[$delta] = check_plain($item['value']);
  }

  return $list_items;
}

/**
 * hook_list_formatter_field_list_info_alter().
 *
 * @param $info
 *  An array of info as declared by hook_list_formatter_field_list_info() to alter
 *  passed by reference.
 */
function hook_list_formatter_field_info_alter(&$info) {
  // Change the callback used for fields from the text module.
  $info['text']['callback'] = 'list_formatter_example_text_callback';
}

/**
 * hook_list_formatter_field_formatter_settings_form_alter().
 */
function hook_list_formatter_field_formatter_settings_form_alter(&$form, &$form_state, $context) {
  // Sample form element here.
}
