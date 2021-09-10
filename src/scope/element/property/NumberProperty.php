<?php

namespace lenz\contentfield\json\scope\element\property;

use craft\fields\Number;
use lenz\contentfield\json\scope\element\Property;

/**
 * Class NumberProperty
 *
 * @property Number $field
 */
class NumberProperty extends Property
{
  /**
   * @var string
   */
  public $definitionType = 'number';

  /**
   * @inheritDoc
   */
  const TARGETS = [Number::class];
}
