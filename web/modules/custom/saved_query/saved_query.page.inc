<?php

/**
 * @file
 * Contains saved_query.page.inc.
 *
 * Page callback for Saved query entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Saved query templates.
 *
 * Default template: saved_query.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_saved_query(array &$variables) {
  // Fetch SavedQuery Entity Object.
  $saved_query = $variables['elements']['#saved_query'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
