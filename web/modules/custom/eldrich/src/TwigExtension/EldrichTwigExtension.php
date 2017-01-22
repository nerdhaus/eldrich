<?php

namespace Drupal\eldrich\TwigExtension;

use Drupal\Core\Render\Renderer;
use Drupal\eldrich\Calculator\StatTreeCalculator;

/**
 * Class EldrichTwigExtension.
 *
 * @package Drupal\eldrich
 */
class EldrichTwigExtension extends \Twig_Extension {


  /**
   * {@inheritdoc}
   */
  public function getTokenParsers() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeVisitors() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return array(
      new \Twig_SimpleFilter('popup', [$this, 'entityPopup']),
      new \Twig_SimpleFilter('classify', [$this, 'Classify']),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTests() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getOperators() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'eldrich.twig.extension';
  }

  /**
   * Twig filter callback: Truncates text with options, and corrects broken HTML.
   *
   * @param $value
   *   The ID of an entity, a fully loaded entity, or a lookup code.
   *
   * @return build
   *   A ready-to-be-rendered markup structure.
   */
  public function entityPopup($value) {
    if (is_object($value)) {
      // Assume it's a loaded entity
      $entity = $value;
      $link_text = $entity->label();
    }
    elseif (is_numeric($value)) {
      // Assume it's a nid
      $entity = Drupal::entityTypeManager()->getStorage('node')->load($value);
      $link_text = $entity->label();
    }
    if (is_string($value)) {
      // Assume it's an aptitude string, because SUUUUPER lazy
      $nid = \Drupal::entityQuery('node')
        ->condition('type', 'stat')
        ->condition('field_code', $value)
        ->execute();

      $entity = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->load(reset($nid));
      $link_text = $value;
    }

    if (!isset($entity)) {
      return $value;
    }

    $uri = $entity->urlInfo();

    $element = [
      '#type' => 'link',
      '#title' => $link_text,
      '#url' => $uri,
      '#options' => $uri->getOptions(),
    ];

    if ($entity->hasField('field_description')) {
      $element['#options']['attributes'] = [
        'title' => $entity->label(),
        'data-toggle' => 'popover',
        'data-content' => strip_tags($entity->field_description->value)
      ];
    }

    return $element;
  }

  public function Classify($value) {
    foreach ($value['class'] as $key => $class) {
      if ($class == 'priority-low') {
        $value['class'][$key] = 'hidden-sm hidden-xs';
      }
      elseif ($class == 'priority-medium') {
        $value['class'][$key] = 'hidden-xs';
      }
    }
    return $value;
  }
}
