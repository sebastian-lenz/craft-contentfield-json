<?php

namespace lenz\contentfield\json\scope\element;

use Craft;
use craft\elements\Category;
use craft\elements\db\ElementQuery;
use craft\models\CategoryGroup;
use craft\models\FieldLayout;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\AbstractStructure;
use lenz\contentfield\json\scope\Project;
use lenz\contentfield\json\scope\StaticProperty;
use yii\base\BaseObject;

/**
 * Class CategoryStructure
 */
class CategoryStructure extends Structure
{
  /**
   * @var CategoryGroup
   */
  public $group;

  /**
   * @inheritDoc
   */
  static $elementType = Category::class;


  /**
   * @inheritDoc
   */
  public function canExport($value): bool {
    return $value instanceof Category && $value->groupId == $this->group->id;
  }

  /**
   * @return array
   */
  public function getContentSettingUids(): array {
    return [$this->group->uid];
  }

  /**
   * @inheritDoc
   */
  public function getDefinitionBaseClass(): string {
    return 'contentfield.Category';
  }

  /**
   * @inheritDoc
   */
  public function getFieldLayout(): FieldLayout {
    return $this->group->getFieldLayout();
  }

  /**
   * @return array
   */
  public function getFilterFixtures(): array {
    return array_merge(parent::getFilterFixtures(), [
      'elementType' => 'category',
      'group' => $this->group->handle,
      'groupId' => $this->group->id,
    ]);
  }

  /**
   * @inheritDoc
   */
  public function getLabel(): string {
    return $this->group->name;
  }

  /**
   * @return ElementQuery
   */
  public function getQuery(): ElementQuery {
    return Category::find()->group($this->group);
  }


  // Protected methods
  // -----------------

  /**
   * @inheritDoc
   */
  protected function loadStaticProperties(): array {
    return array_merge(parent::loadStaticProperties(), [
      new StaticProperty([
        'name' => 'group',
        'structure' => $this,
        'definitionType' => self::literal($this->group->handle),
        'value' => $this->group->handle,
      ])
    ]);
  }


  // Static methods
  // --------------

  /**
   * @param Project $project
   * @return CategoryStructure[]
   */
  static public function createStructures(Project $project): array {
    $result = [];
    foreach (Plugin::MODES as $mode) {
      $suffix = self::modeSuffix($mode);
      $categoryGroup = $project->createUnion([
        'name' => self::join('AnyCategory', $suffix),
      ]);

      foreach (Craft::$app->categories->getAllGroups() as $group) {
        $structure = new CategoryStructure([
          'group' => $group,
          'mode' => $mode,
          'name' => self::join($group->handle, 'Category', $suffix),
          'project' => $project,
        ]);

        $categoryGroup->structures[] = $structure;
        $project->relationTypes['group:' . $group->uid] = $structure;
        $result[] = $structure;
      }
    }

    return $result;
  }
}
