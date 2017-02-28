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
    $variables->timestamp = $this->makeAFTime($comment->getCreatedTime());
  }

  private function makeNetworkId(NodeInterface $character, Comment $comment, $obscure = FALSE) {
    if ($obscure) {
      $unique = (string) $character->id() . (string) $comment->id();
      $identity = $this->hashToCallsign(hash('md2', $unique));
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

  private function makeAFTime($timeStamp = 0) {
    // Jul 4 2005, the UTC Time of Deep Impact's contact with its target
    // https://www.nasa.gov/mission_pages/deepimpact/timeline/index.html

    $fall = 1120455926;
    $time = $timeStamp - $fall;

    $af[] = $time/31556926 % 12;  // to get year
    $af[] = $time / 604800 % 52;  // to get weeks
    $af[] = $time / 3600 % 24;    // to get hours
    $af[] = $time / 60 % 60;      // to get minutes
    // $af[] = $time % 60;        // to get seconds

    return 'AF' . join('.', $af);
  }

  function hashToCallsign($hash) {
    preg_match("/\D\d\d/", $hash, $output_array);
    $str = str_split(array_pop($output_array));

    $nato_alpha = [
      'a' => 'alfa',
      'b' => 'bravo',
      'c' => 'charlie',
      'd' => 'delta',
      'e' => 'echo',
      'f' => 'foxtrot',
      'g' => 'golf',
      'h' => 'hotel',
      'i' => 'india',
      'j' => 'juliett',
      'k' => 'kilo',
      'l' => 'lima',
      'm' => 'mike',
      'n' => 'november',
      'o' => 'oscar',
      'p' => 'papa',
      'q' => 'quebec',
      'r' => 'romeo',
      's' => 'sierra',
      't' => 'tango',
      'u' => 'uniform',
      'v' => 'victor',
      'w' => 'whiskey',
      'y' => 'yankee',
      'z' => 'zulu'
    ];
    $nato_number = [
      '0' => 'zero',
      '1' => 'one',
      '2' => 'two',
      '3' => 'three',
      '4' => 'four',
      '5' => 'five',
      '6' => 'six',
      '7' => 'seven',
      '8' => 'eight',
      '9' => 'niner'
    ];
    $color_number = [
      '0' => 'black',
      '1' => 'blue',
      '2' => 'brown',
      '3' => 'green',
      '4' => 'indigo',
      '5' => 'jade',
      '6' => 'orange',
      '7' => 'pink',
      '8' => 'purple',
      '9' => 'violet'
    ];

    $str[0] = $nato_alpha[$str[0]];
    $str[1] = $nato_number[$str[1]];
    $str[2] = $color_number[$str[2]];

    return join('-', $str);
  }
}
