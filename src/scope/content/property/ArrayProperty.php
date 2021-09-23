<?php

namespace lenz\contentfield\json\scope\content\property;

use lenz\contentfield\json\scope\content\Property;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\fields\ArrayField;
use lenz\contentfield\models\values\ArrayValue;
use Throwable;

/**
 * Class ArrayProperty
 *
 * @property ArrayField $field
 */
class ArrayProperty extends Property
{
  /**
   * @var Property
   */
  public $member;

  /**
   * @inheritDoc
   */
  const TARGETS = [ArrayField::class];


  /**
   * @inheritDoc
   */
  public function init() {
    $this->member = Property::create($this->structure, $this->field->member);
  }

  /**
   * @inheritDoc
   */
  public function exportValue($value, State $state) {
    $result = [];
    $items = $value instanceof ArrayValue ? $value->getVisibleValues() : [];
    foreach ($items as $item) {
      $result[] = $this->member->exportValue($item, $state);
    }

    return $result;
  }

  /**
   * @return string
   * @throws Throwable
   */
  public function getDefinitionType(): string {
    return 'Array<' . $this->member->getDefinitionType() . '>';
  }
}
