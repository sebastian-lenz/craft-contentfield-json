<?php

namespace lenz\contentfield\json\scope\content\property;

use lenz\contentfield\json\events\RegisterFiltersEvent;
use lenz\contentfield\json\scope\content\Property;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\fields\RedactorField;
use lenz\contentfield\models\values\RedactorValue;
use Throwable;
use yii\base\Event;

/**
 * Class RedactorProperty
 *
 * @property RedactorField $field
 */
class RedactorProperty extends Property
{
  /**
   * @var callable[]
   */
  private $_filters;

  /**
   * @var string
   */
  const EVENT_REGISTER_FILTERS = 'registerFilters';

  /**
   * @inheritDoc
   */
  const TARGETS = [RedactorField::class];


  /**
   * @inheritDoc
   */
  public function exportValue($value, State $state) {
    $result = $value instanceof RedactorValue ? $value->jsonSerialize() : null;

    if (!is_null($result)) {
      foreach ($this->getFilters() as $filter) {
        $result = $filter($result);
      }
    }

    return $result;
  }

  /**
   * @return string
   * @throws Throwable
   */
  public function getDefinitionType(): string {
    return 'string';
  }


  // Protected methods
  // -----------------

  /**
   * @return callable[]
   */
  protected function getFilters(): array {
    if (!isset($this->_filters)) {
      $event = new RegisterFiltersEvent([
        'filters' => [
          [RegisterFiltersEvent::class, 'applyAliases']
        ]
      ]);

      Event::trigger($this, self::EVENT_REGISTER_FILTERS, $event);
      $this->_filters = $event->filters;
    }

    return $this->_filters;
  }
}
