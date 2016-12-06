<?php

namespace Drupal\ep_statblock\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'stat_block_table' widget.
 *
 * @FieldWidget(
 *   id = "stat_block_default",
 *   label = @Translation("Default"),
 *   description = @Translation("Default editor for stat blocks"),
 *   field_types = {
 *     "stat_block"
 *   }
 * )
 */
class StatBlockWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $properties = [
      'cog' => t('Cognition'),
      'coo' => t('Coordination'),
      'int' => t('Initiative'),
      'ref' => t('Reflexes'),
      'sav' => t('Savvy'),
      'som' => t('Somatics'),
      'wil' => t('Willpower'),
      'mox' => t('Moxie'),
      'spd' => t('Speed'),
      'dur' => t('Durability'),
      'synthetic' => t('Synthetic'),
    ];

    foreach ($properties as $key => $title) {
      $element[$key] = array(
        '#type' => 'number',
        '#title' => $title,
        '#default_value' => $items[$delta]->{$key},
      );
    }
    $element['synthetic'] = array(
      '#type' => 'checkbox',
      '#title' => $title,
      '#default_value' => $items[$delta]->synthetic,
    );

    return $element;
  }
}
