<?php

namespace lenz\contentfield\json\scope\element\property;

use craft\fields\data\SingleOptionFieldData;
use craft\fields\Dropdown;
use craft\fields\RadioButtons;
use lenz\contentfield\json\scope\element\Property;
use lenz\contentfield\json\scope\State;

/**
 * Class SingleOptionProperty
 *
 * @property Dropdown|RadioButtons $field
 */
class SingleOptionProperty extends Property
{
  /**
   * @inheritDoc
   */
  const TARGETS = [
    Dropdown::class,
    RadioButtons::class,
  ];


  /**
   * @inheritDoc
   */
  public function exportValue($value, $source, State $state): ?string {
    if (!($value instanceof SingleOptionFieldData)) {
      return null;
    }

    return $value->value;
  }

  /**
   * @return string
   */
  public function getDefinitionType(): string {
    $values = self::getOptionLiterals($this->field->options);
    $values[] = 'null';
    return implode(' | ', $values);
  }


  // Static methods
  // --------------

  /**
   * @param array $options
   * @return array
   */
  static public function getOptionLiterals(array $options): array {
    return array_filter(
      array_map(function(array $option) {
        return isset($option['value'])
          ? '"' . $option['value'] . '"'
          : null;
      }, $options)
    );
  }
}
