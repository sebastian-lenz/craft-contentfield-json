<?php

namespace lenz\contentfield\json\scope\content\property;

use lenz\contentfield\json\scope\content\Property;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\fields\TextField;
use Throwable;

/**
 * Class StringProperty
 *
 * @property TextField $field
 */
class StringProperty extends Property
{
  /**
   * @inheritDoc
   */
  const TARGETS = [TextField::class];


  /**
   * @inheritDoc
   */
  public function exportValue($value, State $state): string {
    return (string)$value;
  }

  /**
   * @return string
   * @throws Throwable
   */
  public function getDefinitionType(): string {
    return 'string';
  }
}
