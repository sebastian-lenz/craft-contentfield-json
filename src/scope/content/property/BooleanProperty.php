<?php

namespace lenz\contentfield\json\scope\content\property;

use lenz\contentfield\json\scope\content\Property;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\fields\CheckboxField;
use lenz\contentfield\models\fields\LightswitchField;

/**
 * Class BooleanProperty
 *
 * @property CheckboxField|LightswitchField $field
 */
class BooleanProperty extends Property
{
  /**
   * @inheritDoc
   */
  public $definitionType = 'boolean';

  /**
   * @inheritDoc
   */
  const TARGETS = [
    CheckboxField::class,
    LightswitchField::class
  ];


  /**
   * @inheritDoc
   */
  public function exportValue($value, State $state): bool {
    return !!$value;
  }
}
