<?php

namespace Drupal\eldrich\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_reference_quantity\Plugin\Field\FieldFormatter\EntityReferenceQuantityLabelFormatter;
use Drupal\Core\Link;
use Drupal\Core\Url;


/**
 * Plugin implementation of the 'entity_reference_quantity_label' formatter.
 *
 * @FieldFormatter(
 *   id = "eldrich_citation_formatter",
 *   label = @Translation("Short citation"),
 *   description = @Translation("Display the label of the referenced entities with quantity."),
 *   field_types = {
 *     "entity_reference_quantity"
 *   }
 * )
 */
class EldrichCitationFormatter extends EntityReferenceQuantityLabelFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
        'destination' => 'local',
        'length' => 'full',
      ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['destination'] = array(
      '#type' => 'radios',
      '#options' => [
        'local' => t('Local page'),
        'list' => t('Book list'),
        'remote' => t('Official home page'),
      ],
      '#title' => t('Link to'),
      '#default_value' => $this->getSetting('destination'),
      '#required' => TRUE,
    );
    $elements['length'] = array(
      '#type' => 'radios',
      '#title' => t('Display as'),
      '#options' => [
        'full' => t('Full title'),
        'acronym' => t('Title acronym'),
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

    switch ($this->getSetting('destination')) {
      case 'local':
        $destination = t('local page');
        break;
      case 'list':
        $destination = t('book list');
        break;
      case 'remote':
        $destination = t('official home page');
        break;
    }
    switch ($this->getSetting('length')) {
      case 'full':
        $length = t('normal');
        break;
      case 'acronym':
        $length = t('acronym');
        break;
    }
    $summary[] = t('Link to @destination', array('@destination' => $destination));
    $summary[] = t('Show title as @length', array('@length' => $length));

    return $summary;
  }
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();
    $output_as_link = $this->getSetting('link');
    $destination = $this->getSetting('destination');
    $location = $this->getSetting('location');
    $template = $this->getSetting('template');
    $length = $this->getSetting('length');

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      if ($length == 'acronym') {
        $label = $entity->field_code->value;
      }
      else {
        $label = $entity->label();
      }
      // If the link is to be displayed and the entity has a uri, display a
      // link.
      if ($output_as_link && !$entity->isNew()) {
        if ($destination == 'remote' && !empty($entity->field_home_page)) {
          $uri = $entity->field_home_page->uri;
        }
        elseif ($destination == 'list') {
          $uri = Url::fromRoute('view.bestiary.page_5', [], ['fragment' => $entity->field_code->value]);
        }
        else {
          $uri = UrL::fromRoute('entity.node.canonical', ['node' => $entity->id()]);
        }
      }

      if ($output_as_link && isset($uri) && !$entity->isNew()) {
        $elements[$delta] = [
          '#type' => 'link',
          '#title' => $label,
          '#url' => $uri,
          '#options' => $uri->getOptions(),
        ];

        if (!empty($items[$delta]->_attributes)) {
          $elements[$delta]['#options'] += array('attributes' => array());
          $elements[$delta]['#options']['attributes'] += $items[$delta]->_attributes;
          // Unset field item attributes since they have been included in the
          // formatter output and shouldn't be rendered in the field template.
          unset($items[$delta]->_attributes);
        }
      }
      else {
        $elements[$delta] = array('#plain_text' => $label);
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
