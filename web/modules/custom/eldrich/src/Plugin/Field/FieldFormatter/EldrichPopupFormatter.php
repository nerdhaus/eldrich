<?php

namespace Drupal\eldrich\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\entity_reference_quantity\Plugin\Field\FieldFormatter\EntityReferenceQuantityLabelFormatter;
use Drupal\Core\Form\FormStateInterface;


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

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
        'length' => 'full',
      ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['length'] = array(
      '#type' => 'radios',
      '#title' => t('Display as'),
      '#options' => [
        'full' => t('Full title'),
        'short' => t('Short title'),
      ],
      '#default_value' => $this->getSetting('length'),
      '#required' => TRUE,
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    switch ($this->getSetting('length')) {
      case 'full':
        $length = t('full text');
        break;
      case 'short':
        $length = t('code');
        break;
    }
    $summary[] = t('Show title as @length', array('@length' => $length));

    return $summary;
  }

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    $output_as_link = $this->getSetting('link');
    $template = $this->getSetting('template');
    $location = $this->getSetting('location');

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      $length = $this->getSetting('length');
      if ($length == 'short') {
        if ($entity->hasField('field_lookup_code')) {
          $label = $entity->field_lookup_code->value;
        }
        elseif ($entity->hasField('field_shortname')) {
          $label = $entity->field_shortname->value;
        }
        else {
          $label = $entity->label();
        }
      }
      else {
        $label = $entity->label();
      }

      $elements[$delta] = [
        '#type' => 'link',
        '#title' => $label,
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
