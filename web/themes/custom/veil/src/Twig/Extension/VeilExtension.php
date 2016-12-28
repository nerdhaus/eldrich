<?php

namespace Drupal\veil\Twig\Extension;

/**
 * Custom filters for Twig templates.
 */
class VeilExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return array(
      new \Twig_SimpleFilter('stat_label', [$this, 'getStatLabel']),
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
   * Twig filter callback: Only return a field's label.
   *
   * @param $build
   *   Render array of a field.
   *
   * @return string
   *   The label of a field. If $build is not a render array of a field, NULL is
   *   returned.
   */
  public function getFieldLabel($build) {

  }
}
