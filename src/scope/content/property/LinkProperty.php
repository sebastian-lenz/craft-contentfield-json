<?php

namespace lenz\contentfield\json\scope\content\property;

use Exception;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\content\Property;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\fields\LinkField;
use lenz\contentfield\models\values\LinkValue;
use Throwable;

/**
 * Class LinkProperty
 *
 * @property LinkField $field
 */
class LinkProperty extends Property
{
  /**
   * @inheritDoc
   */
  const TARGETS = [LinkField::class];


  /**
   * @inheritDoc
   * @throws Exception
   */
  public function exportValue($value, State $state) {
    if (!($value instanceof LinkValue) || !$value->hasValue()) {
      return null;
    }

    try {
      $element = $value->getLinkedElement();
      if ($element) {
        $state->dependsOnElement($element);
      }
    } catch (Throwable $error) {
      // Ignore for now
    }

    return [
      'newWindow' => $value->openInNewWindow,
      'type' => $value->type,
      'url' => Plugin::toAlias($value->getUrl()),
    ];
  }

  /**
   * @return string
   * @throws Throwable
   */
  public function getDefinitionType(): string {
    return 'contentfield.Link | null';
  }
}
