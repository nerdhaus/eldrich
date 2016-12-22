<?php
/**
 * @file
 * Contains \Drupal\bootstrap\Plugin\Prerender\Link.
 */

namespace Drupal\veil\Plugin\Prerender;

use Drupal\bootstrap\Annotation\BootstrapConstant;
use Drupal\bootstrap\Annotation\BootstrapPrerender;
use Drupal\bootstrap\Bootstrap;
use Drupal\bootstrap\Utility\Element;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\bootstrap\Plugin\Prerender\Link;

/**
 * Pre-render callback for the "more_link" element type.
 *
 * @ingroup plugins_prerender
 *
 * @BootstrapPrerender("more_link",
 *   action = @BootstrapConstant(
 *     "\Drupal\bootstrap\Bootstrap::CALLBACK_PREPEND"
 *   )
 * )
 *
 * @see \Drupal\Core\Render\Element\Link::preRenderLink()
 */
class MoreLink extends Link { }
