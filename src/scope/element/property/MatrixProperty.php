<?php

namespace lenz\contentfield\json\scope\element\property;

use craft\elements\db\ElementQuery;
use craft\fields\Matrix;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\element\Property;
use lenz\contentfield\json\scope\State;

/**
 * Class MatrixProperty
 *
 * @property Matrix $field
 */
class MatrixProperty extends Property
{
  /**
   * @inheritDoc
   */
  const TARGETS = [Matrix::class];


  /**
   * @inheritDoc
   */
  public function exportValue($value, $source, State $state) {
    if ($value instanceof ElementQuery) {
      $value = $value->all();
      $source->setEagerLoadedElements($this->field->handle, $value);
    }

    if (!is_array($value)) {
      return null;
    }

    return Plugin::toJson($value, Plugin::MODE_DEFAULT, $state);
  }

  /**
   * @return string
   */
  public function getDefinitionType(): string {
    $types = [];
    foreach ($this->field->getBlockTypes() as $blockType) {
      $structures = $this->structure->project->findStructures([
        'elementType' => 'matrixBlock',
        'mode' => Plugin::MODE_DEFAULT,
        'type' => $blockType->handle,
      ]);

      foreach ($structures as $structure) {
        $types[] = $structure->name;
      }
    }

    return 'Array<' . implode('|', $types) . '>';
  }
}
