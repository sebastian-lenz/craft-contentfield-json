<?php

namespace lenz\contentfield\json\scope\element;

use Craft;
use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\Section;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\Project;
use lenz\contentfield\json\scope\StaticProperty;

/**
 * Class EntryStructure
 */
class EntryStructure extends Structure
{
  /**
   * @var Section
   */
  public $section;

  /**
   * @var EntryType
   */
  public $type;

  /**
   * @inheritDoc
   */
  static $elementType = Entry::class;


  /**
   * @inheritDoc
   */
  public function canExport($value): bool {
    return $value instanceof Entry && $value->typeId == $this->type->id;
  }

  /**
   * @return array
   */
  public function getContentSettingUids(): array {
    return [
      $this->type->uid,
      $this->section->uid,
    ];
  }

  /**
   * @inheritDoc
   */
  public function getDefinitionBaseClass(): string {
    return 'contentfield.Entry';
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
      'elementType' => 'entry',
      'section' => $this->section->handle,
      'sectionId' => $this->section->id,
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
    return Entry::find()->section($this->section)->type($this->type);
  }


  // Protected methods
  // -----------------

  /**
   * @inheritDoc
   */
  protected function loadStaticProperties(): array {
    return array_merge(parent::loadStaticProperties(), [
      new StaticProperty([
        'name' => 'section',
        'structure' => $this,
        'definitionType' => self::literal($this->section->handle),
        'value' => $this->section->handle,
      ]),
      new StaticProperty([
        'name' => 'type',
        'structure' => $this,
        'definitionType' => self::literal($this->type->handle),
        'value' => $this->type->handle,
      ]),
    ]);
  }


  // Static methods
  // --------------

  /**
   * @param Project $project
   * @return EntryStructure[]
   */
  static public function createStructures(Project $project): array {
    $result = [];
    foreach (Plugin::MODES as $mode) {
      $suffix = self::modeSuffix($mode);
      $entryGroup = $project->createUnion([
        'name' => self::join('AnyEntry', $suffix),
      ]);

      foreach (Craft::$app->getSections()->getAllSections() as $section) {
        $sectionGroup = $project->createUnion([
          'name' => self::join('Any', $section->handle, 'Entry', $suffix)
        ]);

        if ($mode === Plugin::MODE_DEFAULT) {
          $project->relationTypes['section:' . $section->uid] = $sectionGroup;
        }

        foreach ($section->getEntryTypes() as $type) {
          $structure = new EntryStructure([
            'mode' => $mode,
            'name' => self::join($type->handle, $section->handle, 'Entry', $suffix),
            'project' => $project,
            'section' => $section,
            'type' => $type,
          ]);

          $sectionGroup->structures[] = $structure;
          $entryGroup->structures[] = $structure;
          $result[] = $structure;
        }
      }
    }

    return $result;
  }
}
