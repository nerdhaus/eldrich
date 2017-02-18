<?php

namespace Drupal\ep_import\Plugin\migrate\source;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_source_csv\CSVFileObject;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;

/**
 * Source for CSV.
 *
 * If the CSV file contains non-ASCII characters, make sure it includes a
 * UTF BOM (Byte Order Marker) so they are interpreted correctly.
 *
 * @MigrateSource(
 *   id = "csvs"
 * )
 */
class CSVs extends CSV {
  /**
   * Return a string representing the source file path.
   *
   * @return string
   *   The file path.
   */
  public function __toString() {
    return join(', ', $this->configuration['path']);
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    // Set basics of CSV behavior based on configuration.
    $delimiter = !empty($this->configuration['delimiter']) ? $this->configuration['delimiter'] : ',';
    $enclosure = !empty($this->configuration['enclosure']) ? $this->configuration['enclosure'] : '"';
    $escape = !empty($this->configuration['escape']) ? $this->configuration['escape'] : '\\';
    $iterator = new \AppendIterator();
    if (is_array($this->configuration['path'])) {
      $paths = $this->configuration['path'];
    } else {
      $paths = [$this->configuration['path']];
    }

    foreach ($paths as $path) {
      // File handler using header-rows-respecting extension of SPLFileObject.
      $this->file = new $this->fileClass($path);
      $this->file->setCsvControl($delimiter, $enclosure, $escape);

      // Figure out what CSV column(s) to use. Use either the header row(s) or
      // explicitly provided column name(s).
      if (!empty($this->configuration['header_row_count'])) {
        $this->file->setHeaderRowCount($this->configuration['header_row_count']);

        // Find the last header line.
        $this->file->rewind();
        $this->file->seek($this->file->getHeaderRowCount() - 1);

        $row = $this->file->current();
        foreach ($row as $header) {
          $header = trim($header);
          $column_names[] = [$header => $header];
        }
        $this->file->setColumnNames($column_names);
      }
      // An explicit list of column name(s) will override any header row(s).
      if (!empty($this->configuration['column_names'])) {
        $this->file->setColumnNames($this->configuration['column_names']);
      }
      $iterator->append($this->file);
    }

    return $iterator;
  }
}
