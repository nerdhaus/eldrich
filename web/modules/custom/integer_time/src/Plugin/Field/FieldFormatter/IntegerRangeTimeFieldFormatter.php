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
    if (is_null($number)) {
      return NULL;
    }

    $output = '';

    // Eclipse Phase specific special cases
    if ($number == 0) {
      return "Instantaneous";
    }
    elseif ($number == 3) {
      return "1 Action";
    }

    $units = array(
      '1 week|@count weeks' => 604800,
      '1 day|@count days' => 86400,
      '1 hour|@count hours' => 3600,
      '1 minute|@count minutes' => 60,
      '1 second|@count seconds' => 1,
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
