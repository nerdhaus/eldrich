<?php

namespace Drupal\integer_time\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\NumberWidget;
use Drupal\integer_time\Plugin\Field\FieldFormatter\IntegerTimeFieldFormatter;

/**
 * Plugin implementation of the 'integer_time_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "integer_time_field_widget",
 *   label = @Translation("Timespan string"),
 *   field_types = {
 *     "integer"
 *   }
 * )
 */
class IntegerTimeFieldWidget extends NumberWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    if ($value = isset($items[$delta]->value)) {
      $element['type'] = 'textfield';
      $element['default_value'] = $this->formatNumber($value);
    }
    return array('value' => $element);
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      $values[$delta] = $this->parseTime($value);
    }
    return $values;
  }

  protected function parseTime($string) {
    $matches = array();
    $number = 0;
    preg_match_all("/([0-9]+)([dhms])/", $string, $matches);

    for ($i = 0; $i< count($matches); $i++) {
      switch ($matches[2][$i]) {
        case 'd':
          $number += (int)($matches[1][$i]) * 86400;
          break;
        case 'h':
          $number += (int)($matches[1][$i]) * 3600;
          break;
        case 'm':
          $number += (int)($matches[1][$i]) * 60;
          break;
        case 's':
          $number += (int)($matches[1][$i]);
          break;
      }
    }
    return $number;
  }

  protected function formatNumber($number) {
    $output = '';
    $units = array(
      '1d|@countd' => 86400,
      '1h|@counth' => 3600,
      '1m|@countm' => 60,
      '1s|@counts' => 1,
    );
    foreach ($units as $key => $value) {
      $key = explode('|', $key);
      if ($number >= $value) {
        $output .= ($output ? ' ' : '') . $this->formatPlural(floor($number / $value), $key[0], $key[1], array(), array('langcode' => $langcode));
        $number %= $value;
      }
      elseif ($output) {
        // Break if there was previous output but not any output at this level,
        // to avoid skipping levels and getting output like "1 year 1 second".
        break;
      }
    }
    return $output ? $output : $this->t('0s', array(), array('langcode' => $langcode));
  }
}
