<?php

namespace Drupal\ep_statblock\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'stat_block_table' widget.
 *
 * @FieldWidget(
 *   id = "stat_block_table",
 *   label = @Translation("Table"),
 *   description = @Translation("Tabular editor for stat blocks"),
 *   field_types = {
 *     "stat_block"
 *   }
 * )
 */
class StatBlockTableWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $properties = [
      [
        'cog' => t('Cognition'),
        'coo' => t('Coordination'),
        'int' => t('Initiative'),
        'ref' => t('Reflexes'),
        'sav' => t('Savvy'),
        'som' => t('Somatics'),
        'wil' => t('Willpower'),
        'mox' => t('Moxie')
      ],
      [
        'init' => t('Initiative'),
        'spd' => t('Speed'),
        'luc' => t('Lucidity'),
        'tt' => t('Trauma Threshold'),
        'ir' => t('Insanity Rating'),
        'dur' => t('Durability'),
        'wt' => t('Wound Threshold'),
        'dr' => t('Death Rating')
      ]
    ];

    $widget = [
      '#type' => 'table',
      '#sticky' => FALSE,
      '#no_striping' => TRUE,
      '#attributes' => ['class' => ['stat-block-widget']],
    ];
    $widget['#attached']['library'][] = 'ep_statblock/statblock-widget';

    $row = [];
    foreach ($properties[0] as $key => $title) {
      $row[$key] = array(
        '#type' => 'number',
        '#title' => strtoupper($key),
        '#default_value' => $items[$delta]->{$key},
      );
    }
    $widget[] = $row;

    $row = [];
    foreach ($properties[1] as $key => $title) {
      $row[$key] = array(
        '#type' => 'number',
        '#title' => strtoupper($key),
        '#default_value' => $items[$delta]->{$key},
      );
      if (in_array($key, ['init', 'tt', 'luc', 'ir', 'wt', 'dr'])) {
        $row[$key]['#disabled'] = TRUE;
        $row[$key]['#attributes']['class'][] = 'calculated';
      }
    }
    $widget[] = $row;

    return $widget;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      if (is_array($value[0])) {
        foreach ($value[0] as $key => $subvalue) {
          if (!empty($subvalue)) {
            $values[$delta][$key] = $subvalue;
          }
        }
      }
      if (is_array($value[1])) {
        foreach ($value[1] as $key => $subvalue) {
          if (!empty($subvalue)) {
            $values[$delta][$key] = $subvalue;
          }
        }
      }
      unset($values[$delta][0]);
      unset($values[$delta][1]);
    }
    return $values;
  }
}
