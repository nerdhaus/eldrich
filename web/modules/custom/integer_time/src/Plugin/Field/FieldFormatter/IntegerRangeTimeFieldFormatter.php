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
  protected function numberFormat($number) {
    $output = '';

    // Eclipse Phase specific special cases
    if ($number === 0) {
      return "Instantaneous";
    }
    elseif ($number === 3) {
      return "1 Action";
    }

    $units = array(
      '1d|@countd' => 86400,
      '1h|@counth' => 3600,
      '1m|@countm' => 60,
      '1s|@counts' => 1,
    );
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
