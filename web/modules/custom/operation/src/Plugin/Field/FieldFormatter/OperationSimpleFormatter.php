<?php

namespace Drupal\operation\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\IntegerFormatter;

/**
 * Plugin implementation of the 'operation_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "operation_formatter",
 *   label = @Translation("String"),
 *   field_types = {
 *     "operation"
 *   }
 * )
 */
class IntegerTimeFieldFormatter extends IntegerFormatter {

  /**
   * {@inheritdoc}
   */
  protected function numberFormat($number) {
    return $number;
  }
}