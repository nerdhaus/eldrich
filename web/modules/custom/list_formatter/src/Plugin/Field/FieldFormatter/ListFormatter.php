<?php

/**
 * @file
 * Definition of Drupal\list_formatter\Plugin\field\formatter\List;
 */

namespace Drupal\list_formatter\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'text_default' formatter.
 *
 * @FieldFormatter(
 *   id = "list_formatter",
 *   label = @Translation("List"),
 *   field_types = {},
 * )
 */
class ListFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();

    $settings += [
      'type' => 'ul',
      'class' => 'list-formatter-list',
      'comma_full_stop' => 0,
      'comma_and' => 0,
      'comma_tag' => 'div',
      'term_plain' => 0,
      'comma_override' => 0,
      'separator_custom' => '',
      'separator_custom_tag' => 'span',
      'separator_custom_class' => 'list-formatter-separator',
      'contrib' => [],
    ];

    return $settings;
  }


  /**
   * Implements Drupal\field\Plugin\Type\Formatter\FormatterInterface::settingsForm().
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();

    $elements['type'] = [
      '#title' => $this->t("List type"),
      '#type' => 'select',
      '#options' => $this->listTypes(),
      '#default_value' => $this->getSetting('type'),
      '#required' => TRUE,
    ];
    $elements['comma_and'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Include 'and' before the last item"),
      '#default_value' => $this->getSetting('comma_and'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][type]"]' => ['value' => 'comma'],
        ],
      ],
    ];
    $elements['comma_full_stop'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Append comma separated list with '.'"),
      '#default_value' => $this->getSetting('comma_full_stop'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][type]"]' => ['value' => 'comma'],
        ],
      ],
    ];

    //Override Comma with custom separator.
    $elements['comma_override'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Override comma separator"),
      '#description' => $this->t("Override the default comma separator with a custom separator string."),
      '#default_value' => $this->getSetting('comma_override'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][type]"]' => ['value' => 'comma'],
        ],
      ],
    ];
    $elements['separator_custom'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Custom separator"),
      '#description' => $this->t("Override default comma separator with a custom separator string. You must add your own spaces in this string if you want them. @example", ['@example' => "E.g. ' + ', or ' => '"]),
      '#size' => 40,
      '#default_value' => $this->getSetting('separator_custom'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][comma_override]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['separator_custom_tag'] = [
      '#type' => 'select',
      '#title' => $this->t("separator HTML wrapper"),
      '#description' => $this->t("An HTML tag to wrap the separator in."),
      '#options' => $this->wrapperOptions(),
      '#default_value' => $this->getSetting('separator_custom_tag'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][comma_override]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['separator_custom_class'] = [
      '#title' => $this->t("Separator classes"),
      '#type' => 'textfield',
      '#description' => $this->t("A CSS class to use in the wrapper tag for the separator."),
      '#default_value' => $this->getSetting('separator_custom_class'),
      '#element_validate' => ['_list_formatter_validate_class'],
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][comma_override]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $elements['comma_tag'] = [
      '#type' => 'select',
      '#title' => $this->t("HTML wrapper"),
      '#description' => $this->t("An HTML tag to wrap the list in. The CSS class below will be added to this tag."),
      '#options' => $this->wrapperOptions(),
      '#default_value' => $this->getSetting('comma_tag'),
      '#states' => [
        'visible' => [
          ':input[name=fields[' . $field_name . '][settings_edit_form][settings][type]"]' => ['value' => 'comma'],
        ],
      ],
    ];
    $elements['class'] = [
      '#title' => $this->t("List classes"),
      '#type' => 'textfield',
      '#size' => 40,
      '#description' => $this->t("A CSS class to use in the markup for the field list."),
      '#default_value' => $this->getSetting('class'),
      '#element_validate' => ['_list_formatter_validate_class'],
    ];

    $manager = \Drupal::service('plugin.manager.list_formatter');
    foreach ($manager->getDefinitions() as $id => $definition) {
      $manager->createInstance($id)->additionalSettings($elements, $this->fieldDefinition, $this);
    }

    return $elements;
  }

  /**
   * Implements Drupal\field\Plugin\Type\Formatter\FormatterInterface::settingsSummary().
   */
  public function settingsSummary() {
    $summary = [];

//    $types = $this->listTypes();
//    $summary[] = $types[$this->getSetting('type')];
//
//    if ($this->getSetting('class')) {
//      $summary[] = t("CSS Class") . ': <em>' . check_plain($this->getSetting('class')) . '</em>';
//    }
//
//    if ($this->getSetting('comma_override')) {
//      $summary[] = '<em>*' . t("Comma separator overridden") . '*</em>';
//    }

    return $summary;
  }

  /**
   * Implements Drupal\field\Plugin\Type\Formatter\FormatterInterface::viewElements().
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $module = $this->field['module'];
    $field_type = $this->fieldDefinition->getType();
    $list_formatter_info = $this->fieldListInfo(TRUE);
    $elements = $list_items = array();
    $manager = \Drupal::service('plugin.manager.list_formatter');

    if (in_array($field_type, $list_formatter_info['field_types'][$module])) {
      if ($plugin = $manager->createInstance($module)) {
        $list_items = $plugin->createList($items, $this->fieldDefinition, $langcode);
      }
    }
    else {
      $plugin = $manager->createInstance('default');
      foreach ($items as $delta => $item) {
        $list_items = $plugin->createList($items, $this->fieldDefinition, $langcode);
      }
    }

    // If there are no list items, return and render nothing.
    if (empty($list_items)) {
      return [];
    }

    $type = $this->getSetting('type');

    // CSS classes are checked for validity on submission. drupal_attributes()
    // runs each attribute value through check_plain().
    $classes = explode(' ', $this->getSetting('class'));

    switch ($type) {
      case 'ul':
      case 'ol':
        // Render as one element, item list.
        $elements[] = array(
          '#theme' => 'item_list',
          '#type' => $type,
          '#items' => $list_items,
          '#attributes' => array(
            'class' => $classes,
          ),
        );
      break;
      case 'comma':
        // Render as one element, comma separated list.
        $elements[] = array(
          '#theme' => 'list_formatter_comma',
          '#items' => $list_items,
          '#formatter' => $this,
          '#attributes' => array(
            'class' => $classes,
          ),
        );
      break;
    }

    return $elements;
  }

  /**
   * Returns an array of info to add to hook_field_formatter_info_alter().
   *
   * This iterates through each item returned from fieldListInfo.
   *
   * @param bool $module_key
   *
   * @return array
   *   An array of fields and settings from hook_list_formatter_field_info data
   *   implementations. Containing an aggregated array from all items.
   */
  static public function fieldListInfo($module_key = FALSE) {
    $manager = \Drupal::service('plugin.manager.list_formatter');
    $field_info = array('field_types' => [], 'settings' => []);

    // Create array of all field types and default settings.
    foreach ($manager->getDefinitions() as $id => $definition) {
      $field_types = array();

      if ($module_key) {
        // @todo Add the module and key by plugin id, so they can be independent.
        $module = $definition['module'];
        // Add field types by module.
        foreach ($definition['field_types'] as $type) {
          $field_types[$module][] = $type;
        }
      }
      // Otherwise just merge this, as is. Don't need mergeDeep here.
      else {
        $field_types = array_merge($field_types, $definition['field_types']);
      }

      $field_info['field_types'] = NestedArray::mergeDeep($field_info['field_types'], $field_types);
      $field_info['settings'] = NestedArray::mergeDeep($field_info['settings'], $definition['settings']);
    }

    return $field_info;
  }

  /**
   * Returns a list of available list types.
   *
   * @return array
   *   An options list of types.
   */
  public function listTypes() {
    return [
      'ul' => $this->t("Unordered HTML list (ul)"),
      'ol' => $this->t("Ordered HTML list (ol)"),
      'comma' => $this->t("Comma separated list"),
    ];
  }

  /**
   * Helper method return an array of html tags; formatted for a select list.
   *
   * @return array
   *   A keyed array of available html tags.
   */
  public function wrapperOptions() {
    return [
      $this->t('No HTML tag'),
      'div' => $this->t('Div'),
      'span' => $this->t('Span'),
      'p' => $this->t('Paragraph'),
      'h1' => $this->t('Header 1'),
      'h2' => $this->t('Header 2'),
      'h3' => $this->t('Header 3'),
      'h4' => $this->t('Header 4'),
      'h5' => $this->t('Header 5'),
      'h6' => $this->t('Header 6'),
    ];
  }

}
