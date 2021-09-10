<?php

namespace lenz\contentfield\json\scope\content\property;

use lenz\contentfield\json\scope\content\Property;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\fields\RedactorField;
use lenz\contentfield\models\values\RedactorValue;
use Throwable;

/**
 * Class RedactorProperty
 *
 * @property RedactorField $field
 */
class RedactorProperty extends Property
{
  /**
   * @inheritDoc
   */
  const TARGETS = [RedactorField::class];


  /**
   * @inheritDoc
   */
  public function exportValue($value, State $state) {
    return $value instanceof RedactorValue ? $value->jsonSerialize() : null;
  }

  /**
   * @return string
   * @throws Throwable
   */
  public function getDefinitionType(): string {
    return 'string';
  }
}
