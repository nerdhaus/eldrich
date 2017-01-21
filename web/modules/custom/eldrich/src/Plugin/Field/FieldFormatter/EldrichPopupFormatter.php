<?php

namespace Drupal\eldrich\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\entity_reference_quantity\Plugin\Field\FieldFormatter\EntityReferenceQuantityLabelFormatter;


/**
 * Formats a link with Bootstrap popup markup.
 *
 * @FieldFormatter(
 *   id = "eldrich_popup_formatter",
 *   label = @Translation("Bootstrap popup"),
 *   description = @Translation("Display the label of the referenced entities with quantity."),
 *   field_types = {
 *     "entity_reference", "entity_reference_quantity"
 *   }
 * )
 */
class EldrichPopupFormatter extends EntityReferenceQuantityLabelFormatter {
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    $output_as_link = $this->getSetting('link');
    $template = $this->getSetting('template');
    $location = $this->getSetting('location');

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {

      $elements[$delta] = [
        '#type' => 'link',
        '#title' => $entity->label(),
      ];

      if (!$entity->isNew()) {
        $uri = $entity->urlInfo();
        $elements[$delta]['#url'] = $uri;
        $elements[$delta]['#options'] = $uri->getOptions();
      }

      if (!empty($items[$delta]->_attributes)) {
        $elements[$delta]['#options'] += array('attributes' => array());
        $elements[$delta]['#options']['attributes'] += $items[$delta]->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and shouldn't be rendered in the field template.
        unset($items[$delta]->_attributes);
      }

      if ($entity->hasField('field_description')) {
        $elements[$delta]['#options']['attributes']['title'] = $entity->label();
        $elements[$delta]['#options']['attributes']['data-toggle'] = 'popover';
        $elements[$delta]['#options']['attributes']['data-content'] = strip_tags($entity->field_description->value);
      }

      $elements[$delta]['#cache']['tags'] = $entity->getCacheTags();
    }

    $twig = new \Twig_Environment();
    $values = $items->getValue();
    foreach ($elements as $delta => $entity) {
      if (!empty($values[$delta]['quantity'])) {
        /** @var \Drupal\Core\Template\TwigEnvironment $environment */
        $environment = \Drupal::service('twig');
        $output = $environment->renderInline($template, ['quantity' => $values[$delta]['quantity']]);

        switch ($location) {
          case 'attribute':
            $elements[$delta]['#attributes']['data-quantity'] = $output;
            break;
          case 'pre-title':
            $elements[$delta]['#title'] = $output . $elements[$delta]['#title'];
            break;
          case 'post-title':
            $elements[$delta]['#title'] .= $output;
            break;
          case 'suffix':
            if (!isset($elements[$delta]['#suffix'])) {
              $elements[$delta]['#suffix'] = '';
            }
            $elements[$delta]['#suffix'] = $output;
            break;
        }
      }
    }

    return $elements;
  }
}
