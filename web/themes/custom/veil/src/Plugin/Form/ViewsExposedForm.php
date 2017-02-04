<?php
/**
 * @file
 * Contains \Drupal\bootstrap\Plugin\Form\SearchForm.
 */

namespace Drupal\veil\Plugin\Form;

use Drupal\bootstrap\Annotation\BootstrapForm;
use Drupal\bootstrap\Bootstrap;
use Drupal\bootstrap\Utility\Element;
use Drupal\Core\Form\FormStateInterface;
use Drupal\bootstrap\Plugin\Form\FormBase;

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * @ingroup plugins_form
 *
 * @BootstrapForm("views_exposed_form")
 */
class ViewsExposedForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function alterFormElement(Element $form, FormStateInterface $form_state, $form_id = NULL) {
    $form->actions->submit->addClass(['btn', 'btn-primary']);

    switch ($form['#id']) {
      case 'views-exposed-form-home-page-components-gear-finder':
      case 'views-exposed-form-home-page-components-npc-finder':
        $form->filter->setProperty('title_display', 'invisible');
        $form->cat->setProperty('title_display', 'invisible');

        $form->filter->addClass(['input-sm', 'form-control-inline']);
        $form->cat->addClass(['input-sm', 'form-control-inline']);
        $form->actions->submit->setButtonSize('btn-sm');

        $form->filter->addClass(['col-xs-5', 'col-left-spread'], $form::WRAPPER);
        $form->cat->addClass(['col-xs-5'], $form::WRAPPER);
        $form->actions->submit->addClass(['col-xs-2'], $form::WRAPPER);

        break;

      case 'views-exposed-form-taxonomy-term-catalog':
        $form->filter->addClass(['input-sm', 'form-control-inline']);
        $form->filter->setProperty('title_display', 'invisible');

        $form->type->addClass(['input-sm', 'form-control-inline']);
        $form->type->setProperty('title_display', 'invisible');

        $form->sort_by->addClass(['input-sm', 'form-control-inline']);
        $form->sort_by->setProperty('title_display', 'invisible');

        $form->sort_order->addClass(['input-sm', 'form-control-inline']);
        $form->sort_order->setProperty('title_display', 'invisible');

        $form->actions->submit->setButtonSize('btn-sm');

        $form->addClass(['form-inline']);
        break;

      default:
          $form->addClass(['form-inline']);

    }
  }
}
