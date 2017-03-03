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
    $disabled = $this->getDisabledFields($mode);
    $mode = $this->getSetting('display_style');

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
        '#type' => in_array($key, $disabled) ? 'hidden' : 'number',
        '#step' => 1,
        '#title' => $title,
        '#default_value' => $items[$delta]->{$key},
      );
    }

    if ($this->showCheckBox($mode)) {
      $element['synthetic'] = array(
        '#type' => 'checkbox',
        '#title' => $title,
        '#default_value' => $items[$delta]->synthetic,
      );
    }

    return $element;
  }




  public function showCheckBox($mode) {
    switch ($mode) {
      case 'morph':
      case 'creature':
        return TRUE;
      default:
        return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $options = [
      'morph' => t('Morph stats and bonuses'),
      'mind' => t('Ego stats'),
      'creature' => t('Creature stats'),
      'bonus' => t('Bonuses')
    ];
    $element['display_style'] = [
      '#title' => $this->t('Stat block style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('display_style'),
      '#options' => $options,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
        'display_style' => 'standard',
      ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $display_style = $this->getSetting('display_style');
    $summary = [];
    if (!empty($display_style)) {
      $summary[] = $this->t('Show @subset stats', ['@subset' => $display_style]);
    }
    return $summary;
  }

  private function getDisabledFields($mode) {
    switch ($mode) {
      case 'bonus':
        return [];
        break;

      case 'mind':
        return [
          'init',
          'luc',
          'tt',
          'ir',
          'dur',
          'wt',
          'dr'
        ];
        break;

      case 'morph':
        return [
          'mox',
          'init',
          'luc',
          'tt',
          'ir',
          'wt',
          'dr'
        ];
        break;

      default:
        return [
          'init',
          'luc',
          'tt',
          'ir',
          'wt',
          'dr'
        ];
        break;

    }
  }


  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      foreach ($values[$delta] as $key => $data) {
        if (empty($data)) {
          unset($values[$delta][$key]);
        }
      }
    }

    return $values;
  }
}
