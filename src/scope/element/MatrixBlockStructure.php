<?php

namespace lenz\contentfield\json\scope\element;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\elements\MatrixBlock;
use craft\models\FieldLayout;
use craft\models\MatrixBlockType;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\CustomProperty;
use lenz\contentfield\json\scope\Project;
use lenz\contentfield\json\scope\StaticProperty;

/**
 * Class MatrixBlockStructure
 */
class MatrixBlockStructure extends Structure
{
  /**
   * @var MatrixBlockType
   */
  public $type;

  /**
   * @inheritDoc
   */
  static $elementType = MatrixBlock::class;


  /**
   * @inheritDoc
   */
  public function canExport($value): bool {
    return $value instanceof MatrixBlock && $value->type->id == $this->type->id;
  }

  /**
   * @return array
   */
  public function getContentSettingUids(): array {
    return [$this->type->uid];
  }

  /**
   * @inheritDoc
   */
  public function getDefinitionBaseClass(): string {
    return 'contentfield.MatrixBlock';
  }

  /**
   * @inheritDoc
   */
  public function getFieldLayout(): FieldLayout {
    return $this->type->getFieldLayout();
  }

  /**
   * @return array
   */
  public function getFilterFixtures(): array {
    return array_merge(parent::getFilterFixtures(), [
      'elementType' => 'matrixBlock',
      'type' => $this->type->handle,
      'typeId' => $this->type->id,
    ]);
  }

  /**
   * @inheritDoc
   */
  public function getLabel(): string {
    return $this->type->name;
  }

  /**
   * @return ElementQuery
   */
  public function getQuery(): ElementQuery {
    return MatrixBlock::find()->type($this->type);
  }


  // Protected methods
  // -----------------

  /**
   * @inheritDoc
   */
  protected function loadStaticProperties(): array {
    return [
      new CustomProperty([
        'name' => 'uid',
        'structure' => $this,
        'definitionType' => 'string',
        'handler' => function(Element $element) {
          return $element->uid;
        }
      ]),
      new StaticProperty([
        'name' => 'type',
        'structure' => $this,
        'definitionType' => self::literal($this->type->handle),
        'value' => $this->type->handle,
      ]),
    ];
  }


  // Static methods
  // --------------

  /**
   * @param Project $project
   * @return MatrixBlockStructure[]
   */
  static public function createStructures(Project $project): array {
    $result = [];
    foreach (Plugin::MODES as $mode) {
      $suffix = self::modeSuffix($mode);
      $matrixGroup = $project->createUnion([
        'name' => self::join('AnyMatrixBlock', $suffix),
      ]);

      foreach (Craft::$app->getMatrix()->getAllBlockTypes() as $type) {
        $structure = new MatrixBlockStructure([
          'mode' => $mode,
          'name' => self::join($type->handle, $type->handle, 'MatrixBlock', $suffix),
          'project' => $project,
          'type' => $type,
        ]);

        $matrixGroup->structures[] = $structure;
        $result[] = $structure;
      }
    }

    return $result;
  }
}
