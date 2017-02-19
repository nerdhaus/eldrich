<?php

namespace Drupal\integer_time\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\IntegerFormatter;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\range\Plugin\Field\FieldFormatter\RangeIntegerFormatter;

/**
 * Plugin implementation of the 'integer_time_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "integer_range_time_field_formatter",
 *   label = @Translation("Timespan Range"),
 *   field_types = {
 *     "range_integer"
 *   }
 * )
 */
class IntegerRangeTimeFieldFormatter extends RangeIntegerFormatter {
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
        'abbreviate' => FALSE,
      ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['abbreviate'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Abbreviate durations'),
      '#default_value' => $this->getSetting('abbreviate'),
      '#weight' => 13,
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    if ($this->getSetting('abbreviate')) {
      $summary[] = $this->t('Abbreviated durations');
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  protected function numberFormat($number) {
    if (is_null($number)) {
      return NULL;
    }

    $output = '';

    // Eclipse Phase specific special cases
    if ($number == 0) {
      return $this->getSetting('abbreviate') ? "Instant" : "Instantaneous";
    }
    elseif ($number == 3) {
      return $this->getSetting('abbreviate') ? "1 Turn" : "1 Action Turn";
    }

    if ($this->getSetting('abbreviate')) {
      $units = array(
        '1w|@countw' => 604800,
        '1d|@countd' => 86400,
        '1h|@counth' => 3600,
        '1m|@countm' => 60,
        '1s|@counts' => 1,
      );
    }
    else {
      $units = array(
        '1 week|@count weeks' => 604800,
        '1 day|@count days' => 86400,
        '1 hour|@count hours' => 3600,
        '1 minute|@count minutes' => 60,
        '1 second|@count seconds' => 1,
      );
    }

    foreach ($units as $key => $value) {
      $key = explode('|', $key);
      if ($number >= $value) {
        $output .= ($output ? ' ' : '') . $this->formatPlural(floor($number / $value), $key[0], $key[1], array());
        $number %= $value;
      }
      elseif ($output) {
        // Break if there was previous output but not any output at this level,
        // to avoid skipping levels and getting output like "1 year 1 second".
        break;
      }
    }
    return $output ? $output : NULL;
  }
}
