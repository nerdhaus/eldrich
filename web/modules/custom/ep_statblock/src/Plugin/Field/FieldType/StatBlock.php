<?php

namespace Drupal\ep_statblock\Plugin\Field\FieldType;

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
 *   id = "stat_block",
 *   label = @Translation("Stat block"),
 *   description = @Translation("Eclipse Phase stat block"),
 *   category = @Translation("Eclipse Phase"),
 *   default_widget = "stat_block_table",
 *   default_formatter = "stat_block_table",
 * )
 */
class StatBlock extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['cog'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Cognition'));
    $properties['coo'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Coordination'));
    $properties['int'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Intuition'));
    $properties['ref'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Reflexes'));
    $properties['sav'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Savvy'));
    $properties['som'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Somatics'));
    $properties['wil'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Willpower'));
    $properties['mox'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Moxie'));
    $properties['spd'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Speed'));
    $properties['dur'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Durability'));
    $properties['synthetic'] = DataDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Synthetic'));

    $properties['init'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Initiative'));
    $properties['luc'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Lucidity'));
    $properties['tt'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Trauma Threshold'));
    $properties['ir'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Insanity Rating'));
    $properties['wt'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Wound Threshold'));
    $properties['dr'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Death Rating'));

/*
    $properties['init'] = DataDefinition::create('integer')
      ->setLabel(t('Initiative'))
      ->setComputed(TRUE)
      ->setReadOnly(TRUE)
      ->setClass('Drupal\ep_statblock\StatDeriver')
      ->setSetting('property', 'init');

    $properties['tt'] = DataDefinition::create('integer')
      ->setLabel(t('Trauma Threshold'))
      ->setComputed(TRUE)
      ->setReadOnly(TRUE)
      ->setClass('Drupal\ep_statblock\StatDeriver')
      ->setSetting('property', 'tt');

    $properties['luc'] = DataDefinition::create('integer')
      ->setLabel(t('Lucidity'))
      ->setComputed(TRUE)
      ->setReadOnly(TRUE)
      ->setClass('Drupal\ep_statblock\StatDeriver')
      ->setSetting('property', 'luc');

    $properties['ir'] = DataDefinition::create('integer')
      ->setLabel(t('Insanity Rating'))
      ->setComputed(TRUE)
      ->setReadOnly(TRUE)
      ->setClass('Drupal\ep_statblock\StatDeriver')
      ->setSetting('property', 'ir');

    $properties['wt'] = DataDefinition::create('integer')
      ->setLabel(t('Wound Threshold'))
      ->setComputed(TRUE)
      ->setReadOnly(TRUE)
      ->setClass('Drupal\ep_statblock\StatDeriver')
      ->setSetting('property', 'wt');

    $properties['dr'] = DataDefinition::create('integer')
      ->setLabel(t('Death Rating'))
      ->setComputed(TRUE)
      ->setReadOnly(TRUE)
      ->setClass('Drupal\ep_statblock\StatDeriver')
      ->setSetting('property', 'dr');
*/


    $properties['db'] = DataDefinition::create('integer')
      ->setLabel(t('Damage Bonus'))
      ->setComputed(TRUE)
      ->setReadOnly(TRUE)
      ->setClass('Drupal\ep_statblock\StatDeriver')
      ->setSetting('property', 'db');

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'cog' => ['type' => 'int'],
        'coo' => ['type' => 'int'],
        'int' => ['type' => 'int'],
        'ref' => ['type' => 'int'],
        'sav' => ['type' => 'int'],
        'som' => ['type' => 'int'],
        'wil' => ['type' => 'int'],
        'mox' => ['type' => 'int'],
        'spd' => ['type' => 'int'],
        'dur' => ['type' => 'int'],
        'init' => ['type' => 'int'],
        'luc' => ['type' => 'int'],
        'tt' => ['type' => 'int'],
        'ir' => ['type' => 'int'],
        'wt' => ['type' => 'int'],
        'dr' => ['type' => 'int'],
        'synthetic' => ['type' => 'int'],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['cog'] = mt_rand(10, 20);
    $values['coo'] = mt_rand(10, 20);
    $values['int'] = mt_rand(10, 20);
    $values['ref'] = mt_rand(10, 20);
    $values['sav'] = mt_rand(10, 20);
    $values['som'] = mt_rand(10, 20);
    $values['wil'] = mt_rand(10, 20);
    $values['mox'] = mt_rand(1, 5);
    $values['spd'] = mt_rand(1, 3);
    $values['dur'] = mt_rand(30, 45);
    $values['synth'] = mt_rand(0, 1);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    if (
      empty($this->values['cog']) &&
      empty($this->values['coo']) &&
      empty($this->values['int']) &&
      empty($this->values['ref']) &&
      empty($this->values['sav']) &&
      empty($this->values['som']) &&
      empty($this->values['wil']) &&
      empty($this->values['mox']) &&
      empty($this->values['spd']) &&
      empty($this->values['dur']) &&
      empty($this->values['init']) &&
      empty($this->values['luc']) &&
      empty($this->values['tt']) &&
      empty($this->values['ir']) &&
      empty($this->values['wt']) &&
      empty($this->values['dr']) &&
      empty($this->values['synthetic'])
    ) {
      return TRUE;
    }
    return FALSE;
  }

}
