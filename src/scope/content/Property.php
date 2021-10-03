<?php

namespace lenz\contentfield\json\scope\content;

use lenz\contentfield\json\scope\AbstractProperty;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\fields\AbstractField;
use lenz\contentfield\models\values\InstanceValue;

/**
 * Class Property
 *
 * @property Structure $structure
 */
class Property extends AbstractProperty
{
  /**
   * @var AbstractField
   */
  public $field;

  /**
   * @var string[]|Property[]
   */
  static $IMPLEMENTATIONS = [
    property\ArrayProperty::class,
    property\EnumerationPropery::class,
    property\InstanceProperty::class,
    property\LayoutProperty::class,
    property\LinkProperty::class,
    property\NumberProperty::class,
    property\RedactorProperty::class,
    property\ReferenceProperty::class,
    property\StringProperty::class,
  ];

  /**
   * @var string[]|AbstractField[]
   */
  const TARGETS = [];


  /**
   * @inheritDoc
   * @param InstanceValue $source
   */
  public function export(object $target, $source, State $state) {
    $target->{$this->name} = $this->exportValue(
      $source->offsetGet($this->field->name),
      $state
    );
  }

  /**
   * @param mixed $value
   * @param State $state
   * @return mixed
   */
  public function exportValue($value, State $state) {
    return $this->field->getSerializedValue($value);
  }


  // Static methods
  // --------------

  /**
   * @param Structure $structure
   * @param AbstractField $field
   * @return Property
   */
  static function create(Structure $structure, AbstractField $field): Property {
    $propertyClass = Property::class;
    foreach (self::$IMPLEMENTATIONS as $implementation)
    foreach ($implementation::TARGETS as $target) {
      if (is_a($field, $target)) {
        $propertyClass = $implementation;
        break 2;
      }
    }

    return new $propertyClass([
      'name' => $field->name,
      'field' => $field,
      'structure' => $structure,
    ]);
  }
}
