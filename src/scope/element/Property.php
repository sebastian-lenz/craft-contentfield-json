<?php

namespace lenz\contentfield\json\scope\element;

use craft\base\Element;
use craft\base\FieldInterface;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\AbstractProperty;
use lenz\contentfield\json\scope\AbstractStructure;
use lenz\contentfield\json\scope\State;

/**
 * Class Property
 *
 * @property Structure $structure
 */
class Property extends AbstractProperty
{
  /**
   * @var FieldInterface
   */
  public $field;

  /**
   * @var array|string[]|Property[]
   */
  static $IMPLEMENTATIONS = [
    property\ContentProperty::class,
    property\MatrixProperty::class,
    property\NumberProperty::class,
    property\RelationProperty::class,
    property\StringProperty::class,
  ];

  /**
   * @var string[]|FieldInterface[]
   */
  const TARGETS = [];


  /**
   * @inheritDoc
   * @param Element $source
   */
  public function export(object $target, $source, State $state) {
    $handle = $this->field->handle;
    $target->{$this->name} = $this->exportValue($source->$handle, $source, $state);
  }

  /**
   * @param mixed $value
   * @return mixed
   */
  public function exportValue($value, $source, State $state) {
    return $value;
  }


  // Static methods
  // --------------

  /**
   * @param Structure $structure
   * @param FieldInterface $field
   * @return Property
   */
  static function create(Structure $structure, FieldInterface $field): Property {
    $propertyClass = Property::class;
    foreach (self::$IMPLEMENTATIONS as $implementation)
    foreach ($implementation::TARGETS as $target) {
      if (is_a($field, $target)) {
        $propertyClass = $implementation;
        break 2;
      }
    }

    return new $propertyClass([
      'field' => $field,
      'isEnabled' => $structure->mode == Plugin::MODE_DEFAULT,
      'name' => $field->handle,
      'structure' => $structure,
    ]);
  }
}
