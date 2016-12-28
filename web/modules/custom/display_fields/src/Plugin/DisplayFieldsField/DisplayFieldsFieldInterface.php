<?php

/**
 * @file
 * Contains \Drupal\display_fields\Plugin\DisplayFieldsField\DisplayFieldsFieldInterface.
 */

namespace Drupal\display_fields\Plugin\DisplayFieldsField;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Interface for Display fields plugins.
 */
interface DisplayFieldsFieldInterface extends ConfigurablePluginInterface {

  /**
   * Renders a field.
   *
   * @param array $entities
   *   An array of entities.
   * @param array $display_field
   *   The saved display field configuration, as in DisplayFieldsFieldInterface::createForm().
   * @param array $display_settings
   *   The display settings as saved from manage overview form,
   *   @see DisplayFieldsFieldInterface::buildFieldFormRow().
   * @param string $parent_entity
   *   The parent entity that are being view().
   * @param string $view_mode
   *   The view mode the parent entity is build with.
   * @param string $language
   *   The language the parent entity is build with.
   *
   * @return array
   *   A render array of the fields for the entities given.
   */
  public function getFieldBuild($entities, $display_field, $display_settings, $parent_entity, $view_mode, $language);

  /**
   * Returns the create form for this display field.
   *
   * @param array $form.
   *   The part of the form for adding the createForm for this fields.
   *   DisplayField module is responsible for saving those values.
   * @param FormStateInterface $form_state.
   * @param array $parents.
   *   The parent keys of this part of the form.
   * @return array
   *   A render array containing the form.
   */
  public function createForm($form, FormStateInterface $form_state, $parents = array());

  /**
   * Handle the submit of the create form for this display field,
   * display_field module is responsible for saving those values,
   * but you can format the value in form_state here before is going to be saved.
   *
   * @param array $form.
   *   The part of the form for adding the createForm for this fields.
   *   DisplayField module is responsible for saving those values.
   * @param FormStateInterface $form_state.
   * @param $parents.
   *   The parent keys of this part of the form.
   *
   */
  public function createFormSubmit($form, FormStateInterface $form_state, $parents = array());

  /**
   * Render the field row on the manage display form.
   *
   * @param string $field_name.
   *   The display field machine name.
   * @param array $field.
   *   The display field configuration.
   * @param array $field_display_settings.
   *   The current display field view settings for this field and view mode.
   * @param string $view_mode.
   *   The view mode context for the manage display form.
   * @param FormStateInterface $form_state.
   * @param array $complete_form.
   *   The complete form of the manage display overview.
   *
   * @return array
   *   A render array containing the form.
   */
  public function buildFieldFormRow($field_name, $field, $field_display_settings, $view_mode, FormStateInterface $form_state, &$complete_form);

}
