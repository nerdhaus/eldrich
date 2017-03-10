<?php
namespace Drupal\eldrich\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'entity_reference_override_text' widget.
 *
 * @FieldWidget(
 *   id = "entity_reference_override_text",
 *   label = @Translation("Free Entry"),
 *   field_types = {
 *     "entity_reference_override",
 *     "entity_reference",
 *   },
 *   multiple_values = TRUE
 * )
 */


class EntityReferenceOverrideTextWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $options = [];
    foreach (node_type_get_types() as $key => $type) {
      $options[$type->id()] = $type->label();
    }
    $element['types'] = [
      '#title' => $this->t('Content types to search'),
      '#type' => 'select',
      '#multiple' => TRUE,
      '#default_value' => $this->getSetting('types'),
      '#options' => $options,
    ];
    $element['short_name'] = [
      '#title' => $this->t('Fall back to short name'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('short_name'),
    ];
    $element['delimiter'] = [
      '#title' => $this->t('Entry delimiter'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('delimiter'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
        'types' => [],
        'delimiter' => ', ',
        'short_name' => TRUE,
      ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $short_name = $this->getSetting('short_name');
    $types = $this->getSetting('types');
    $delimiter = $this->getSetting('delimiter');

    $summary = [];
    if ($short_name) {
      $summary[] = $this->t('Search titles and short names');
    }
    else {
      $summary[] = $this->t('Search titles');
    }
    if (!empty($types)) {
      $summary[] = $this->t('In node types: @types', ['@types' => join(', ', $types)]);
    }
    if (!empty($delimiter)) {
      $summary[] = $this->t("Separated by '@delimiter'", ['@delimiter' => $delimiter]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $delimiter = $this->getSetting('delimiter');

    $default = [];
    foreach ($items as $item) {
      $default[] = $this->formatCustomValues($item);
    }

    $element['value'] = $element + array(
        '#type' => 'textarea',
        '#rows' => 6,
        '#default_value' => join(', ', $default),
        '#description' => t('References should be entered as: @format', ['@format' => 'Entity Title' . $delimiter . 'Extra text'])
      );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = $this->parseCustomValues($values);
    return $values;
  }


  public function parseCustomValues($value) {
    $short_name = $this->getSetting('short_name');
    $types = $this->getSetting('types');
    $delimiter = $this->getSetting('delimiter');

    $results = [];

    if (is_array($value)) {
      $value = join(',', $value);
    }
    $values = explode(',', $value);
    foreach ($values as $key => $value) {
      $split = explode($delimiter, $value);
      $values[$key]['title'] = trim($split[0]);
      if (isset($split[1])) {
        $values[$key]['override'] = trim($split[1]);
      }
    }

    foreach ($values as $key => $data) {
      $query = \Drupal::entityQuery('node');

      if (!empty($types)) {
        $query->condition('type', $types, 'IN');
      }

      if ($short_name) {
        $or = $query->orConditionGroup()
          ->condition('title', $data['title'])
          ->condition('field_short_name', $data['title']);
        $query->condition($or);
      }
      else {
        $query->condition('title', $data['title']);
      }

      if ($ids = $query->execute()) {
        $data['target_id'] = reset($ids);
        $results[] = $data;
      }
    }

    return $results;
  }

  function formatCustomValues(FieldItemInterface $item) {
    $delimiter = $this->getSetting('delimiter');

    // Spits out values as Reference Entity: Override Text
    $output = FALSE;
    if ($skill = $item->entity) {
      $output = $item->entity->label();
      if (!empty($item->override)) {
        $output .= $delimiter . ucwords($item->override);
      }
    }
    return $output;
  }
}
