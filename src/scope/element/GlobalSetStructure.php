<?php

namespace lenz\contentfield\json\scope\element;

use Craft;
use craft\elements\db\ElementQuery;
use craft\elements\GlobalSet;
use craft\models\FieldLayout;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\AbstractStructure;
use lenz\contentfield\json\scope\Project;
use lenz\contentfield\json\scope\StaticProperty;
use yii\base\BaseObject;

/**
 * Class GlobalSetStructure
 */
class GlobalSetStructure extends Structure
{
  /**
   * @var GlobalSet
   */
  public $globalSet;


  /**
   * @inheritDoc
   */
  static $elementType = GlobalSet::class;


  /**
   * @inheritDoc
   */
  public function canExport($value): bool {
    return $value instanceof GlobalSet && $value->id == $this->globalSet->id;
  }

  /**
   * @return array
   */
  public function getContentSettingUids(): array {
    return [$this->globalSet->uid];
  }

  /**
   * @inheritDoc
   */
  public function getDefinitionBaseClass(): string {
    return 'contentfield.GlobalSet';
  }

  /**
   * @inheritDoc
   */
  public function getFieldLayout(): FieldLayout {
    return $this->globalSet->getFieldLayout();
  }

  /**
   * @return array
   */
  public function getFilterFixtures(): array {
    return array_merge(parent::getFilterFixtures(), [
      'globalSet' => $this->globalSet->handle,
      'globalSetId' => $this->globalSet->id,
      'elementType' => 'globalSet',
    ]);
  }

  /**
   * @inheritDoc
   */
  public function getLabel(): string {
    return $this->globalSet->name;
  }

  /**
   * @return ElementQuery
   */
  public function getQuery(): ElementQuery {
    return GlobalSet::find()->handle($this->globalSet->handle);
  }


  // Protected methods
  // -----------------

  /**
   * @inheritDoc
   */
  protected function loadStaticProperties(): array {
    return array_merge(parent::loadStaticProperties(), [
      new StaticProperty([
        'name' => 'globalSet',
        'structure' => $this,
        'definitionType' => self::literal($this->globalSet->handle),
        'value' => $this->globalSet->handle,
      ])
    ]);
  }


  // Static methods
  // --------------

  /**
   * @param Project $project
   * @return GlobalSetStructure[]
   */
  static public function createStructures(Project $project): array {
    $result = [];
    foreach (Plugin::MODES as $mode) {
      $suffix = self::modeSuffix($mode);
      $globalSetGroup = $project->createUnion([
        'name' => self::join('AnyGlobalSet', $suffix),
      ]);

      foreach (Craft::$app->globals->getAllSets() as $globalSet) {
        $structure = new GlobalSetStructure([
          'globalSet' => $globalSet,
          'mode' => $mode,
          'name' => self::join($globalSet->handle, 'GlobalSet', $suffix),
          'project' => $project,
        ]);

        $globalSetGroup->structures[] = $structure;
        $result[] = $structure;
      }
    }

    return $result;
  }
}
