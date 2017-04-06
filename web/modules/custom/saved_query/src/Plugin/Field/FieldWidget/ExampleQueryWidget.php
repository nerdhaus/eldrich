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
 *   id = "example_saved_query_widget",
 *   label = @Translation("Example Query Editor"),
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

    $element['#type'] = 'fieldset';
    $element['#collapsible'] = TRUE;

    $element['entity_type'] = [
      '#type' => 'value',
      '#value' => 'node',
    ];
    $element['interval'] = [
      '#type' => 'value',
      '#value' => 360 * 2,
    ];

    $element['age'] = [
      '#title' => t('Conditions'),
      '#type' => 'textarea',
      '#default_value' => isset($items[$delta]->conditions) ? Yaml::encode($items[$delta]->conditions) : NULL,
    ];
    $element['age'] = [
      '#title' => t('Conditions'),
      '#type' => 'textarea',
      '#default_value' => isset($items[$delta]->conditions) ? Yaml::encode($items[$delta]->conditions) : NULL,
    ];


    $element['sorts'] = [
      '#title' => t('Sort By'),
      '#type' => 'select',
      '#options' => array(
        'date-asc' => t('Chronological'),
        'date-dec' => t('Blog-style'),
        'title-asc' => t('Alphabetical'),
      ),
      '#default_value' => isset($items[$delta]->sorts) ? Yaml::encode($items[$delta]->sorts) : NULL,
    ];
    $element['limit'] = [
      '#title' => t('Result limit'),
      '#type' => 'number',
      '#default_value' => isset($items[$delta]->limit) ? $items[$delta]->limit : NULL,
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

    foreach (['conditions', 'sorts'] as $serialized) {
      if (!empty($values[$serialized])) {
        $values[$serialized] = Yaml::decode($values[$serialized]);
      }
      else {
        unset($values[$serialized]);
      }
    }
  }
}
