<?php

namespace Drupal\ep_complex_values\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'stat_block' field type.
 *
 * @FieldType(
 *   id = "damage_value",
 *   label = @Translation("Damage"),
 *   description = @Translation("Eclipse Phase damage"),
 *   category = @Translation("Eclipse Phase"),
 *   default_widget = "damage_widget",
 *   default_formatter = "damage_text"
 * )
 */
class DamageValue extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['dice'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Dice'));
    $properties['ap'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Armor Penetration'));
    $properties['mod_function'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Modifier function'));
    $properties['modifier'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Modifier'));

    $properties['notation'] = DataDefinition::create('string')
      ->setLabel(t('Notation'))
      ->setComputed(TRUE)
      ->setReadOnly(TRUE)
      ->setClass('Drupal\ep_complex_values\DamageFieldNotation');

    $properties['average'] = DataDefinition::create('integer')
      ->setLabel(t('Average Damage'))
      ->setComputed(TRUE)
      ->setReadOnly(TRUE)
      ->setClass('Drupal\ep_complex_values\DamageAverage');

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'dice' => ['type' => 'int', 'unsigned' => FALSE],
        'ap' => ['type' => 'int', 'unsigned' => FALSE],
        'mod_function' => ['type' => 'varchar', 'length' => 5, 'default' => '+'],
        'modifier' => ['type' => 'int', 'unsigned' => TRUE],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if (
      empty($this->values['dice']) &&
      empty($this->values['ap']) &&
      empty($this->values['modifier']) &&
      empty($this->values['mod_function'])
    ) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Simple helper to spit out
   *
   * @param array $values
   * @return string
   */
  public static function formatDamageNotation(Array $values) {
    $value = [];
    if (!empty($values['dice'])) {
      $value['dice'] = $values['dice'] . 'd10';
    }
    if (!empty($values['modifier'])) {
      $value['modifier'] = $values['mod_function'] . ' ' . $values['modifier'];
    }

    if (!empty($values['ap'])) {
      $value['ap'] = (-1 * $values['ap']) . 'AP';
    }

    return join(' ', $value);
  }
}
