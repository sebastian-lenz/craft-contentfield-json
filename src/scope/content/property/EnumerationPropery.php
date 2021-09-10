<?php

namespace lenz\contentfield\json\scope\content\property;

use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\content\Property;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\enumerations\StaticEnumeration;
use lenz\contentfield\models\fields\AbstractEnumerationField;
use lenz\contentfield\models\values\EnumerationValue;
use lenz\contentfield\models\values\LinkValue;
use Throwable;

/**
 * Class EnumerationPropery
 *
 * @property AbstractEnumerationField $field
 */
class EnumerationPropery extends Property
{
  /**
   * @var string
   */
  public $definitionType = 'string|number';

  /**
   * @inheritDoc
   */
  const TARGETS = [AbstractEnumerationField::class];


  /**
   * @inheritDoc
   */
  public function exportValue($value, State $state) {
    if (!($value instanceof EnumerationValue)) {
      return null;
    }

    return $value->getValue();
  }

  /**
   * @return string
   * @throws Throwable
   */
  public function getDefinitionType(): string {
    $enumeration = $this->field->getEnumeration();
    if (!($enumeration instanceof StaticEnumeration)) {
      return $this->definitionType;
    }

    $keys = $enumeration->getOptionKeys();
    if (!count($keys)) {
      return $this->definitionType;
    }

    return implode(' | ', array_map(function ($key) {
      return "'$key'";
    }, $keys));
  }
}
