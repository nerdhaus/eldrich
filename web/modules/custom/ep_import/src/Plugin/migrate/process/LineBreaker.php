<?php

namespace Drupal\ep_import\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * This plugin turns linebreaks into paragraph tags.
 *
 * @MigrateProcessPlugin(
 *   id = "line_breaker"
 * )
 */
class LineBreaker extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if (empty($value)) {
      return $value;
    }

    // Strip out nasty linebreaks, a problem in our source data
    $value = preg_replace("/\s?[\n\r]{1,10}/", "</p>\r<p>", $value);
    $value = preg_replace("/\s?[\n\r]/", "", $value);

    $value = "<p>" . $value . "</p>";

    return $value;
  }
}
