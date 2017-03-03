<?php
/**
 * @file
 * Contains \Drupal\bootstrap\Plugin\Preprocess\Page.
 */

namespace Drupal\veil\Plugin\Preprocess;

use Drupal\bootstrap\Utility\Variables;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessInterface;
use Drupal\bootstrap\Plugin\Preprocess\PreprocessBase;
use Drupal\node\NodeInterface;
use Drupal\comment\Entity\Comment;
use Drupal\Component\Utility\Html;


/**
 * Pre-processes variables for the "comment" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("comment")
 */
class ICComment extends PreprocessBase implements PreprocessInterface {

  /**
   * {@inheritdoc}
   */
  public function preprocessVariables(Variables $variables) {
    /** @var Comment $comment */
    $comment = $variables->element['#comment'];

    $character = $comment->get('field_character')->entity;
    $obscure = $comment->get('field_obscure_id')->value;

    $variables->identity = $this->makeNetworkId($character, $comment, $obscure);
    $variables->reputation = $this->getCharacterRep($character);
    $variables->timestamp = eldrich_af_time($comment->getCreatedTime(), 3);
  }

  private function makeNetworkId(NodeInterface $character, Comment $comment, $obscure = FALSE) {
    if ($obscure) {
      $identity = make_obscured_name($character->id(), $comment->id());
    }
    else {
      $identity = Html::cleanCssIdentifier(strtolower($character->label()));
    }

    return $character->toLink($identity);
  }

  private function getCharacterRep(NodeInterface $character) {
    $reps = [];

    if ($character->hasField('field_identity') && !$character->get('field_identity')->isEmpty()) {
      $identity = $character->get('field_identity')->entity;
      if ($identity->hasField('field_rep') && !$identity->get('field_rep')->isEmpty()) {
        foreach ($identity->get('field_rep') as $network) {
          $reps[] = $network->entity->get('field_symbol')->value . $network->quantity;
        }
      }
    }

    if (count($reps)) {
      return strtoupper(join('.', $reps));
    }
    else {
      return '';
    }
  }
}
