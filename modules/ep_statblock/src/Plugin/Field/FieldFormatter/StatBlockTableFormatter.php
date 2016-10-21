<?php

namespace Drupal\ep_statblock\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'stat_block_table' formatter.
 *
 * @FieldFormatter(
 *   id = "stat_block_table",
 *   label = @Translation("Table"),
 *   field_types = {
 *     "stat_block",
 *   }
 * )
 */
class StatBlockTableFormatter extends FormatterBase  {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
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

      $elements[$delta] = [
        '#type' => 'table',
        '#sticky' => FALSE,
        '#attributes' => ['class' => ['stat-block-formatter']],
        '#attached' => ['library' => ['ep_statblock/statblock-widget']]
      ];

      $row = [];
      foreach ($properties[0] as $key => $title) {
        $row[$key] = array(
          '#type' => 'html_tag',
          '#tag' => 'abbr',
          '#attributes' => ['title' => $title],
          '#value' => strtoupper($key),
        );
      }
      $elements[$delta][] = $row;

      $row = [];
      foreach ($properties[0] as $key => $title) {
        $row[$key] = array(
          '#type' => 'markup',
          '#markup' => $this::nullStatString($item->{$key}),
        );
      }
      $elements[$delta][] = $row;

      $row = [];
      foreach ($properties[1] as $key => $title) {
        $row[$key] = array(
          '#type' => 'html_tag',
          '#tag' => 'abbr',
          '#attributes' => ['title' => $title],
          '#value' => strtoupper($key),
        );
      }
      $elements[$delta][] = $row;

      $row = [];
      foreach ($properties[1] as $key => $title) {
        $row[$key] = array(
          '#type' => 'markup',
          '#markup' => $this::nullStatString($item->{$key}),
        );
      }
      $elements[$delta][] = $row;
    }

    return $elements;
  }

  public function nullStatString($value) {
    return empty($value) ? '-' : $value;
  }
}
