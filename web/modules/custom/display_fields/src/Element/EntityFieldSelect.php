<?php

/**
 * @file
 * Contains \Drupal\display_fields\Element\EntityFieldSelect.
 */

namespace Drupal\display_fields\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Component\Utility\NestedArray;

/**
 * Provides a form element for selection of entity fields through entities and referenced entities.
 *
 * Declaration of the following element are mandatory:
 *   $form['your_element'] = array(
      '#type' => 'entity_field_select',
      '#title' => t('Select a field'),
      '#entity_type' => $this->getEntityTypeId(),
      '#bundle' => $this->bundle(),
    );
 *
 * Return multiple values :
 *
 *   $element['reference_key']
 *    - Contains a string that contains the path to the field through the parent entity_type,
 *      each part of the keys are separated via ':'. And reference key have infos splited with '|'.
 *      Examples :
 *      {field_article_references|node|article:field_tags|taxonomy_term|tags:name}
 *        Refer to the term name referenced via two reference entities of specified type|bundle fields.
 *      {field_image}
 *        Refer to the field_image of the source entity type / bundle.
 *      {field_references|node|*:author|user|user:user_picture}
 *        Refer to the user_picture via two referenced entities of specified types but for every bundle of the node references.
 *
 *   $element['entity_type']
 *    - Contains the entity_type of the field selected.
 *   $element['bundle'];
 *    - Contains the bundle of the field selected. This value can be a joker '*'
 *   $element['source_entity_type']
 *    - Contains the source entity type as passed in the form element array definitions.
 *   $element['source_bundle']
 *    - Contains the source entity bundle as passed in the form element array definitions.
 *   $element['field_name']
 *    - Contains the machine_name of the field selected.
 *
 * @FormElement("entity_field_select")
 */
class EntityFieldSelect extends FormElement {

  protected $entityTypeId;

  protected $bundle;

  /**
   * {@inheritdoc}
   *
   * See @ElementInfoManagerInterface::getInfo().
   */
  public function getInfo() {
    $class = get_class($this);
    return array(
      '#input' => TRUE,
      '#process' => array(
        array($class, 'processEntityFieldSelect'),
      ),
      '#title_display' => 'form-no-label',
      '#theme_wrappers' => array('form_element'),
    );
  }

  /**
   * Expand the entity_field_select form element into the final form elements.
   */
  public static function processEntityFieldSelect(&$element, FormStateInterface $form_state, &$complete_form) {
    $source_entity_type = $element['#entity_type'];
    $source_bundle = $element['#bundle'];

    $values = $form_state->getValue($element['#array_parents']);
    $triggering_element = $form_state->getTriggeringElement();
    $default_value = array(
      'reference_key' => '',
      'source_entity_type' => $source_entity_type,
      'source_bundle' => $source_bundle,
      'entity_type' => $source_entity_type,
      'bundle' => $source_bundle,
      'field_name' => '',
    );

    if (isset($element['#default_value']) && empty($values) &&
        empty($triggering_element['#df_action'])) {
      // Set the value from #default_value.
      $default_value = array_merge($default_value, $element['#default_value']);
    }

    $values = array(
      'reference_key' => isset($values['reference_key']) ? $values['reference_key'] : $default_value['reference_key'],
      'source_entity_type' => isset($values['source_entity_type']) ? $values['source_entity_type'] : $default_value['source_entity_type'],
      'source_bundle' => isset($values['source_bundle']) ? $values['source_bundle'] : $default_value['source_bundle'],
      'entity_type' => isset($values['entity_type']) ? $values['entity_type'] : $default_value['entity_type'],
      'bundle' => isset($values['bundle']) ? $values['bundle'] : $default_value['bundle'],
      'field_name' => isset($values['field_name']) ? $values['field_name'] : $default_value['field_name'],
    );
    $element['#prefix'] = '<div id="' . EntityFieldSelect::getElementHTMLId($element, $complete_form) . '">';
    $element['#suffix'] = '</div>';
    if ($element['#title_display'] != 'invisible') {
      $element['#prefix'] .= '<h5>' . $element['#title'] . '</h5>';
    }
    $element['reference_key'] = array(
      '#type' => 'hidden',
      '#value' => $values['reference_key'],
    );
    $element['entity_type'] = array(
      '#type' => 'hidden',
      '#value' => $values['entity_type'],
    );
    $element['bundle'] = array(
      '#type' => 'hidden',
      '#value' => $values['bundle'],
    );
    $element['source_entity_type'] = array(
      '#type' => 'hidden',
      '#value' => $values['source_entity_type'],
    );
    $element['source_bundle'] = array(
      '#type' => 'hidden',
      '#value' => $values['source_bundle'],
    );
    $element['reference_breadcrumb'] = array(
      '#type' => 'fieldset',
      '#title' => (string) t('Entity field selection'),
      '#attributes' => array(),
      'element' => array(
        '#markup' => !empty($values['field_name']) ? EntityFieldSelect::getBreadcrumbReferences($values) : '',
      ),
      '#weight' => -10,
    );
    if (empty($values['reference_key']) ||
        substr($values['reference_key'], strlen($values['reference_key']) - 1, 1) === ':') {

      $fields_options = EntityFieldSelect::getOptionsFromValues($values);
      $element['field_name'] = array(
        '#type' => 'select',
        '#title' => (string) t('Select a field'),
        '#options' => $fields_options,
        '#weight' => -1,
        '#ajax' => array(
          'callback' => array(get_called_class(), 'ajaxEntityFieldSelect'),
          'wrapper' => EntityFieldSelect::getElementHTMLId($element, $complete_form),
        ),
        '#weight' => -5,
        '#required' => isset($element['#required']) ? $element['#required'] : FALSE,
        '#df_action' => 'field_selection',
        '#attributes' => array(
          'autocomplete' => 'off',
        )
      );

      $element['field_name_reset'] = array(
        '#type' => 'hidden',
        '#value' => (string) t('Reset'),
        '#limit_validation_errors' => array(),
        '#df_action' => 'reset',
        '#ajax' => array(
          'callback' => array(get_called_class(), 'ajaxEntityFieldSelect'),
          'wrapper' => EntityFieldSelect::getElementHTMLId($element, $complete_form),
        ),
      );
    }
    else {
      $element['field_name'] = array(
        '#type' => 'hidden',
        '#value' => $values['field_name'],
        '#df_action' => 'field_selection',
        '#ajax' => array(
          'callback' => array(get_called_class(), 'ajaxEntityFieldSelect'),
          'wrapper' => EntityFieldSelect::getElementHTMLId($element, $complete_form),
        ),
      );

      $element['field_name_reset'] = array(
        '#type' => 'button',
        '#value' => (string) t('Reset'),
        '#df_action' => 'reset',
        '#limit_validation_errors' => array('field_name'),
        '#weight' => -1,
        '#ajax' => array(
          'callback' => array(get_called_class(), 'ajaxEntityFieldSelect'),
          'wrapper' => EntityFieldSelect::getElementHTMLId($element, $complete_form),
        ),
      );
    }
    $element['#element_validate'] = array(array(get_called_class(), 'validateEntityFieldSelect'));
    $element['#tree'] = TRUE;
    return $element;
  }

  public static function getElementHTMLId($element, $complete_form) {
    return 'entity-field-select-wrapper-' . md5(implode('', $element['#array_parents']) . $complete_form['form_id']['#value']);
  }

  /**
   * Ajax form callback.
   */
  public static function ajaxEntityFieldSelect($form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    if (isset($triggering_element['#df_action'])) {
      array_pop($triggering_element['#array_parents']);
      return NestedArray::getValue($form, $triggering_element['#array_parents']);
    }
  }

  /**
   * Validates a password_confirm element.
   */
  public static function validateEntityFieldSelect(&$element, FormStateInterface $form_state, &$complete_form) {
    $reference_key = $element['reference_key']['#value'];
    $entity_type = $element['entity_type']['#value'];
    $bundle = $element['bundle']['#value'];
    $source_entity_type = $element['source_entity_type']['#value'];
    $source_bundle = $element['source_bundle']['#value'];
    $field_name = $element['field_name']['#value'];


    $triggering_element = $form_state->getTriggeringElement();

    if (!isset($triggering_element['#df_action'])) {
      return;
    }

    if ($triggering_element['#df_action'] === 'reset' || empty($field_name) || $field_name == '**RESET**') {
      $form_state->setValueForElement($element['entity_type'], $source_entity_type);
      $form_state->setValueForElement($element['bundle'], $source_bundle);
      $form_state->setValueForElement($element['field_name'], '');
      $form_state->setValueForElement($element['reference_key'], '');

      return $element;
    }

    if ((strpos($field_name, '|') === FALSE && (empty($reference_key)) ||
        (strpos($field_name, '|') === FALSE && substr($reference_key, strlen($reference_key) - 1, 1) === ':'))) {
      // Field name selected to be added.
      $form_state->setValueForElement($element['reference_key'], $reference_key . $field_name);
    }
    elseif (strpos($field_name, '|') !== FALSE) {
      // A field has been selected through a reference.
      // We add the field_name|entity_type|bundle at this point.
      // And we add a ':' suffix to indicate that is a reference
      // and a field name must be select after it.
      $field_name_infos = explode('|', $field_name);

      $form_state->setValueForElement($element['entity_type'], $field_name_infos[1]);
      $form_state->setValueForElement($element['bundle'], $field_name_infos[2]);
      $form_state->setValueForElement($element['reference_key'], $reference_key . $field_name . ':');
    }

    return $element;
  }

  /**
   * Get options fields.
   *
   * @param array $values
   *   The form element values.
   *
   * @return array
   *   An array of options for the element[field_name].
   */
  public static function getOptionsFromValues($values) {
    $reference_key = $values['reference_key'];
    $current_entity_type = $values['entity_type'];
    $current_bundle = $values['bundle'];
    $current_field_name = $values['field_name'];

    if ($current_bundle == '*') {
      $bundles = \Drupal::entityManager()->getBundleInfo($current_entity_type);
      $fields = array();
      foreach ($bundles as $bundle_name => $bundle) {
      	$fields = array_merge($fields, \Drupal::entityManager()->getFieldDefinitions($current_entity_type, $bundle_name));
      }
    }
    else {
      $fields = \Drupal::entityManager()->getFieldDefinitions($current_entity_type, $current_bundle);
    }
    $fields_options = array();
    foreach ($fields as $field_name => $field) {
      if (!$field->isDisplayConfigurable('view')) {
        // @todo add a $display_context settings to display only the field that have a display configurable context.
      }

      $fieldStorage = $field->getFieldStorageDefinition();
      $field_cardinality = $fieldStorage->getCardinality();
      // Add this field options.
      $fields_options[(string) t('Fields')][$field_name] = $field->getLabel();

      // Add 'actions' options, for select referenced fields.
      if (in_array($field->getType(), EntityFieldSelect::entityReferenceFieldType())) {

        // Set options for referenced entities.
        $field_settings = $field->getSettings();
        $target_entity_type = $field_settings['target_type'];

        $targetEntityType = \Drupal::entityManager()->getDefinition($target_entity_type);

        // If this is a content entity.
        if ($targetEntityType->isSubClassOf('\Drupal\Core\Entity\ContentEntityInterface')) {
          $target_entity_type_bundles = EntityFieldSelect::getReferenceFieldsAllowedBundles($field->getType(), $field_settings);
          $target_bundles_infos = \Drupal::entityManager()->getBundleInfo($target_entity_type);
          if (empty($target_entity_type_bundles)) {
            // Empty target_bundles means all bundles. Get those.
            $target_entity_type_bundles = array_keys($target_bundles_infos);
          }
          // Create "actions" options.
          $fields_options[(string) t('Entity reference')][$field_name . '|' . $target_entity_type . '|' . '*'] = $field->getLabel() . ' : ' . t('All entities referenced', array('@bundle_label' => strtolower((string) t('All bundles'))));
          if ($field_cardinality !== 1) {
            $fields_options[(string) t('Entity reference')][$field_name . '|' . $target_entity_type . '|' . '*' . '|0'] = $field->getLabel() . ' : ' . t('First of all entities referenced', array('@bundle_label' => strtolower((string) t('All bundles'))));
          }
          foreach ($target_entity_type_bundles as $target_bundle) {
            $bundle_label = $target_bundles_infos[$target_bundle]['label'];
            $fields_options[(string) t('Entity reference')][$field_name . '|' . $target_entity_type . '|' . $target_bundle] = $field->getLabel() . ' : ' . t('All entities referenced of bundle: @bundle_label', array('@bundle_label' => strtolower($bundle_label)));
            if ($field_cardinality !== 1) {
              $fields_options[(string) t('Entity reference')][$field_name . '|' . $target_entity_type . '|' . $target_bundle. '|0'] = $field->getLabel() . ' : ' . t('First of all entities referenced of bundle: @bundle_label', array('@bundle_label' => strtolower($bundle_label)));
            }
          }
        }
      }
    }

    // Add an extra options to reset the values.
    if (!empty($reference_key)) {
      $fields_options[(string) t('Action')] = array(
        '**RESET**' => (string) t('Reset'),
      );
    }

    return $fields_options;
  }

  /**
   * Return the allowed bundles for an entity reference field type.
   *
   * @param string $type
   *   A field type.
   * @param array $settings
   *   The field definition settings.
   *
   * @return array
   *   An array of bundles that can be referenced by the entity reference field.
   */
  public static function getReferenceFieldsAllowedBundles($type, $settings) {
    if ($type == 'entity_reference') {
      return !empty($settings['handler_settings']['target_bundles']) ? $settings['handler_settings']['target_bundles'] : array();
    }
    elseif ($type == 'taxonomy_term_reference') {
      $bundles = array();
      foreach ($settings['allowed_values'] as $allowed_value) {
        $bundles[] = $allowed_value['vocabulary'];
      }
      return $bundles;
    }
    return array();
  }

  /**
   * The entity reference field type, this element can handle.
   * @return array
   *   An array of field types.
   */
  public static function entityReferenceFieldType() {
    return array(
      'taxonomy_term_reference',
      'entity_reference',
    );
  }

  /**
   * Get a display breadcrumb to indicate the parent reference selected.
   * @param array $values
   * @return string
   *   The breadcrumb html.
   */
  public static function getBreadcrumbReferences($values) {
    $reference_key = $values['reference_key'];
    $source_entity_type = $values['source_entity_type'];
    $source_bundle = $values['source_bundle'];
    $current_entity_type = $source_entity_type;
    $current_bundle = $source_bundle;

    if (empty($reference_key)) {
      return t('No field selected');
    }
    $references = explode(':', $reference_key);
    $breadcrumb = array();
    foreach ($references as $field_name) {
      if (empty($field_name)) {
        $breadcrumb[] = '?';
        continue;
      }
      if (strpos($field_name, '|') !== FALSE) {
        $field_name_infos = explode('|', $field_name);
        $fields = \Drupal::entityManager()->getFieldDefinitions($current_entity_type, $current_bundle);
        $field = $fields[$field_name_infos[0]];
        $current_entity_type = $field_name_infos[1];
        $breadcumb_desc = $field->getLabel();
        if (isset($field_name_infos[3]) && $field_name_infos[3] == 0) {
          $breadcumb_desc .= ' [0]';
        }
        $breadcrumb[] = str_repeat('&nbsp;&nbsp;', count($breadcrumb) + 1) . '> ' . $breadcumb_desc;

      }
      else {
        $fields = \Drupal::entityManager()->getFieldDefinitions($current_entity_type, $current_bundle);
        if (isset($fields[$field_name])) {
          $field = $fields[$field_name];
          $breadcumb_desc = $field->getLabel();
          $breadcrumb[] = str_repeat('&nbsp;&nbsp;', count($breadcrumb) + 1) . '> <strong>' . $breadcumb_desc . '</strong>';
        }
      }
    }

    $source_bundles = \Drupal::entityManager()->getBundleInfo($source_entity_type);
    $bundle_label = $source_bundles[$source_bundle]['label'];
    return $bundle_label . '<br>' . implode('<br>', $breadcrumb);
  }
}
