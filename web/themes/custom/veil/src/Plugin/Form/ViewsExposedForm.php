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
        $form->name->addClass('input-sm');
        $form->name->setProperty('title_display', 'invisible');
        $form->name->setProperty('placeholder', t('Title'));

        $form->cat->addClass('input-sm');
        $form->cat->setProperty('title_display', 'invisible');

        $form->actions->submit->setButtonSize('btn-sm');
        break;
    }
  }
}
