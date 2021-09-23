<?php

namespace lenz\contentfield\json\scope\element\property;

use craft\fields\Checkboxes;
use craft\fields\data\MultiOptionsFieldData;
use craft\fields\data\OptionData;
use craft\fields\MultiSelect;
use lenz\contentfield\json\scope\element\Property;
use lenz\contentfield\json\scope\State;

/**
 * Class MultiOptionsProperty
 *
 * @property Checkboxes|MultiSelect $field
 */
class MultiOptionsProperty extends Property
{
  /**
   * @inheritDoc
   */
  const TARGETS = [
    Checkboxes::class,
    MultiSelect::class,
  ];


  /**
   * @inheritDoc
   */
  public function exportValue($value, $source, State $state): array {
    $result = [];
    if (!($value instanceof MultiOptionsFieldData)) {
      return $result;
    }

    foreach ($value as $selectedValue) {
      if ($selectedValue instanceof OptionData) {
        $result[] = $selectedValue->value;
      }
    }

    return $result;
  }

  /**
   * @return string
   */
  public function getDefinitionType(): string {
    $types = SingleOptionProperty::getOptionLiterals($this->field->options);
    return 'Array<' . implode(' | ', $types) . '>';
  }
}
