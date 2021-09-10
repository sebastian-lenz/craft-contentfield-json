<?php

namespace lenz\contentfield\json\scope\content\property;

use lenz\contentfield\helpers\grids\BootstrapGrid;
use lenz\contentfield\json\scope\content\Property;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\fields\LayoutField;
use lenz\contentfield\models\values\LayoutColumnValue;
use lenz\contentfield\models\values\LayoutValue;
use Throwable;

/**
 * Class LayoutProperty
 *
 * @property LayoutField $field
 */
class LayoutProperty extends Property
{
  /**
   * @var Property
   */
  public $member;

  /**
   * @inheritDoc
   */
  const TARGETS = [LayoutField::class];


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
    if (!($value instanceof LayoutValue)) {
      return null;
    }

    $grid = $value->getGrid();
    $grid = $grid instanceof BootstrapGrid ? $grid : null;

    $columns = array_map(function(LayoutColumnValue $column) use ($grid, $state) {
      return (object)[
        'className' => $grid ? $grid->getColumnClassName($column) : '',
        'value' => $this->member->exportValue($column->getValue(), $state),
      ];
    }, $value->getColumns());

    return (object)[
      'preset' => $value->getPreset(),
      'columns' => $columns,
    ];
  }

  /**
   * @return string
   * @throws Throwable
   */
  public function getDefinitionType(): string {
    return 'contentfield.Layout<' . $this->member->getDefinitionType() . '>';
  }
}
