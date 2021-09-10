<?php

namespace lenz\contentfield\json\scope\content\property;

use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\content\Property;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\fields\NumberField;
use lenz\contentfield\models\values\LinkValue;
use Throwable;

/**
 * Class NumberProperty
 *
 * @property NumberField $field
 */
class NumberProperty extends Property
{
  /**
   * @inheritDoc
   */
  const TARGETS = [NumberField::class];


  /**
   * @inheritDoc
   */
  public function exportValue($value, State $state) {
    if (!is_numeric($value)) {
      return 0;
    }

    return $value;
  }

  /**
   * @return string
   * @throws Throwable
   */
  public function getDefinitionType(): string {
    return 'number';
  }
}
