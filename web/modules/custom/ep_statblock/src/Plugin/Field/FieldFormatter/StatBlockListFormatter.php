<?php

namespace Drupal\ep_statblock\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'stat_block_table' formatter.
 *
 * @FieldFormatter(
 *   id = "stat_block_list",
 *   label = @Translation("Definition List"),
 *   field_types = {
 *     "stat_block",
 *   }
 * )
 */
class StatBlockListFormatter extends FormatterBase  {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $properties = [
      'cog' => t('Cognition'),
      'coo' => t('Coordination'),
      'int' => t('Initiative'),
      'ref' => t('Reflexes'),
      'sav' => t('Savvy'),
      'som' => t('Somatics'),
      'wil' => t('Willpower'),
      'mox' => t('Moxie'),
      'init' => t('Initiative'),
      'spd' => t('Speed'),
      'luc' => t('Lucidity'),
      'tt' => t('Trauma Threshold'),
      'ir' => t('Insanity Rating'),
      'dur' => t('Durability'),
      'wt' => t('Wound Threshold'),
      'dr' => t('Death Rating')
    ];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'dl',
        '#attributes' => ['class' => ['dl-statblock']],
        '#attached' => ['library' => ['ep_statblock/statblock-widget']]
      ];

      foreach ($properties as $key => $title) {
        $elements[$delta][] = array(
          '#type' => 'html_tag',
          '#tag' => 'dt',
          '#attributes' => ['title' => $title],
          '#value' => strtoupper($key),
        );
        $elements[$delta][] = array(
          '#type' => 'html_tag',
          '#tag' => 'dd',
          '#value' => $this::nullStatString($item->{$key}),
        );
      }
    }

    return $elements;
  }

  public function nullStatString($value) {
    return empty($value) ? '-' : $value;
  }
}
