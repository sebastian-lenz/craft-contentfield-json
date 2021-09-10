<?php

namespace lenz\contentfield\json\scope\content\property;

use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\content\Property;
use lenz\contentfield\json\scope\content\Structure;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\fields\InstanceField;
use lenz\contentfield\models\schemas\AbstractSchema;
use lenz\contentfield\models\values\InstanceValue;
use Throwable;

/**
 * Class InstanceProperty
 *
 * @property InstanceField $field
 */
class InstanceProperty extends Property
{
  /**
   * @inheritDoc
   */
  const TARGETS = [InstanceField::class];


  /**
   * @inheritDoc
   */
  public function exportValue($value, State $state) {
    return $value instanceof InstanceValue
      ? Plugin::toJson($value, Plugin::MODE_DEFAULT, $state)
      : null;
  }

  /**
   * @return string
   * @throws Throwable
   */
  public function getDefinitionType(): string {
    $schemas = array_map(function(AbstractSchema $schema) {
      return $schema->qualifier;
    }, $this->field->getResolvedSchemas());

    $types = array_map(function(Structure $structure) {
      return $structure->name;
    }, $this->structure->project->findSchemas($schemas));

    return count($types) ? implode('|', $types) : 'unknown';
  }
}
