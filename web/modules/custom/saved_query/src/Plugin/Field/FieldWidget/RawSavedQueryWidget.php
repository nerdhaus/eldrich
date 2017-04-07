<?php

namespace Drupal\saved_query\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Yaml;


/**
 * Plugin implementation of the 'raw_saved_query_widget' widget.
 *
 * @FieldWidget(
 *   id = "raw_saved_query_widget",
 *   label = @Translation("Raw Query Editor"),
 *   field_types = {
 *     "saved_query_field"
 *   },
 *   multiple_values = FALSE
 * )
 */
class RawSavedQueryWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $entity_types = \Drupal::entityManager()->getDefinitions();
    $entity_type_options = array();
    foreach ($entity_types as $key => $data) {
      if ($data->getGroup() == 'content') {
        $entity_type_options[$key] = $data->getLabel(); // Lazy for the moment.
      }
    }

    $element['#type'] = 'fieldset';
    $element['#collapsible'] = TRUE;

    $element['entity_type'] = [
      '#title' => t('Entity type'),
      '#options' => $entity_type_options,
      '#type' => 'select',
      '#required' => TRUE,
      '#default_value' => isset($items[$delta]->entity_type) ? $items[$delta]->entity_type : 'node',
    ];
    $element['conditions'] = [
      '#title' => t('Conditions'),
      '#type' => 'textarea',
      '#default_value' => empty($items[$delta]->conditions) ? NULL : Yaml::encode($items[$delta]->conditions),
    ];
    $element['sorts'] = [
      '#title' => t('Sorts'),
      '#type' => 'textarea',
      '#default_value' => empty($items[$delta]->sorts) ? NULL : Yaml::encode($items[$delta]->sorts),
    ];
    $element['limit'] = [
      '#title' => t('Result limit'),
      '#type' => 'number',
      '#default_value' => isset($items[$delta]->limit) ? $items[$delta]->limit : NULL,
    ];

    $intervals = array(
      (360) => t('1 hour'),
      (360 * 2) => t('2 hour'),
      (360 * 6) => t('6 hours'),
      (360 * 24) => t('1 day'),
      (360 * 24 * 7) => t('1 week'),
    );
    $element['interval'] = [
      '#title' => t('Refresh interval'),
      '#options' => $intervals,
      '#type' => 'select',
      '#default_value' => isset($items[$delta]->interval) ? $items[$delta]->interval : NULL,
    ];
    $element['refresh_now'] = [
      '#title' => t('Refresh on save'),
      '#type' => 'checkbox',
    ];
    $element['refreshed'] = [
      '#title' => t('Last refreshed'),
      '#type' => 'hidden',
      '#value' => isset($items[$delta]->refreshed) ? $items[$delta]->refreshed : NULL,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    // In a more complex widget, this would be responsible for turning
    // incoming form values into a structured array like so:
    //
    // $values['conditions'] = array(
    //   "entity_field" => "value",
    //   "entity_field_2" => array(
    //     "value" => 1,
    //     "operator" => ">",
    //   ),
    //   'or' => array(
    //     "entity_field_a" => "value",
    //     "entity_field_b" => "value",
    //   );
    // );
    //
    // See https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Entity!Query!QueryInterface.php/function/QueryInterface%3A%3Acondition/8.2.x
    // For details on valid field syntax. You can do lots of crazy stuff.
    //
    // AND / OR conditions are not yet handled, but won't confuse the query builder.
    // Sorts are handled similarly, though the structure is simpler:
    //
    // $values['sorts'] = array(
    //   "entity_field" => "ASC",
    //   "entity_field_2" => "DESC"
    // );
    //
    // ORDER BY RANDOM() isn't supported, because you're a bad person.

    foreach ($values as &$item) {
      foreach ($item as $key => $value) {
        if (empty($value)) {
          unset($item[$key]);
        }
        elseif (in_array($key, ['conditions', 'sorts'])) {
          $item[$key] = Yaml::decode($value);
        }
      }
    }

    return $values;
  }
}
