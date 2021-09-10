<?php

namespace lenz\contentfield\json\scope\element\property;

use craft\fields\PlainText;
use craft\redactor\Field as Redactor;
use lenz\contentfield\json\scope\element\Property;
use lenz\contentfield\json\scope\State;

/**
 * Class StringProperty
 *
 * @property PlainText $field
 */
class StringProperty extends Property
{
  /**
   * @var string
   */
  public $definitionType = 'string';

  /**
   * @inheritDoc
   */
  const TARGETS = [
    PlainText::class,
    Redactor::class,
  ];


  /**
   * @inheritDoc
   */
  public function exportValue($value, $source, State $state) {
    return (string)$value;
  }
}
