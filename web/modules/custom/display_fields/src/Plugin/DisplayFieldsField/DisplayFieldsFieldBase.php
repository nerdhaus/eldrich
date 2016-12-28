<?php

/**
 * @file
 * Contains \Drupal\display_fields\Plugin\DisplayFieldsField\DisplayFieldsFieldBase.
 */

namespace Drupal\display_fields\Plugin\DisplayFieldsField;

use Drupal\Component\Plugin\PluginBase as ComponentPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\display_fields\Plugin\DisplayFieldsField\DisplayFieldsFieldInterface;

/**
 * Base class for display fields plugins.
 */
abstract class DisplayFieldsFieldBase extends ComponentPluginBase implements DisplayFieldsFieldInterface {

  /**
   * Constructs a Display Fields field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration += $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function createForm($form, FormStateInterface $form_state, $parents = array()) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function createFormSubmit($form, FormStateInterface $form_state, $parents = array()) {}

  /**
   * {@inheritdoc}
   */
  public function getFieldBuild($entities, $display_field, $display_settings, $parent_entity, $view_mode, $language) {
  	return array();
  }

  /**
   * {@inheritdoc}
   */
  public function buildFieldFormRow($field_name, $field, $field_display_settings, $view_mode, FormStateInterface $form_state, &$complete_form) {
  	return array();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
   return array();
  }

   /**
   * {@inheritdoc}
    */
  public function getConfiguration() {
    return $this->configuration;
  }

   /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->configuration;
  }


  /**
   * Gets the current entity type.
   */
  public function getEntityTypeId() {
    if (isset($this->configuration['entity_type'])) {
      return $this->configuration['entity_type'];
    }
    else {
      return '';
    }
  }

  /**
   * Gets the current bundle.
   */
  public function bundle() {
    return $this->configuration['bundle'];
  }

  /**
   * @inheritdoc
   */
  public function calculateDependencies() {
    // By default there are no dependencies
  }

}
