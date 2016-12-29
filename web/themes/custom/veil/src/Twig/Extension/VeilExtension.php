<?php

namespace Drupal\veil\Twig\Extension;

use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * Custom filters for Twig templates.
 */
class VeilExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return array(
      new \Twig_SimpleFilter(
        'safe_truncate',
        [$this, 'safeTruncate'],
        array('pre_escape' => 'html', 'is_safe' => array('html'))
      ),
      new \Twig_SimpleFilter('lookup_label', [$this, 'getLookupLabel']),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'veil';
  }

  /**
   * Twig filter callback: Truncates text with options, and corrects broken HTML.
   *
   * @param $text
   *   A string to be truncated.
   * @param $max_length
   *   Maximum length of the string, the rest gets truncated.
   * @param $word_boundary:
   *   Trim only on a word boundary.
   * @param $ellipsis
   *   Show an ellipsis (…) at the end of the trimmed string.
   * @param $html
   *   Make sure that the html is correct.

   * @return string
   *   Truncated text with fixed HTML.
   */
  public function safeTruncate($text, $max_length = 200, $word_boundary = TRUE, $ellipsis = TRUE, $html = TRUE) {
    FieldPluginBase::trimText($text, $max_length, $word_boundary, $ellipsis, $html);
  }


  /**
   * Twig filter callback: Truncates text with options, and corrects broken HTML.
   *
   * @param $lookup
   *   The ID of a lookup code, or a fully loaded Entity.
   *
   * @return build
   *   A ready-to-be-rendered markup structure.
   */
  public function lookupLabel($lookup) {
    return ['#markup' => 'TODO'];
  }
}
