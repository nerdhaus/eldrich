<?php
/**
 * Created by PhpStorm.
 * User: jeff
 * Date: 4/6/17
 * Time: 12:12 AM
 */

namespace Drupal\saved_query;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\TypedData;

/**
 * A computed property for processing text with a format.
 *
 * Required settings (below the definition's 'settings' key) are:
 *  - source property: The name of the property containing the serialized data.
 */
class QuerySerialized extends TypedData {

  /**
   * The unserialized data.
   *
   * @var array
   */
  protected $data;

  /**
   * {@inheritdoc}
   */
  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $name, $parent);

    if ($definition->getSetting('source property') === NULL) {
      throw new \InvalidArgumentException("The definition's 'source property' key has to specify the name of the property containing the raw data.");
    }
  }

  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::getValue().
   */
  public function getValue($langcode = NULL) {
    if (!isset($this->data)) {
      $item = $this->getParent();
      $value = $item->{($this->definition->getSetting('source property'))};

      if (empty($value)) {
        $this->data = [];
      }
      else {
        $this->data = unserialize($value);
      }
    }
    return $this->data;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value, $notify = TRUE) {
    if (is_array($value)) {
      $this->data = $value;

      // Notify the parent of any changes.
      if ($notify && isset($this->parent)) {
        $this->parent->onChange($this->name);
      }
    }
  }
}
