<?php

namespace lenz\contentfield\json\scope\element;

use Craft;
use craft\base\Volume;
use craft\elements\Asset;
use craft\elements\db\ElementQuery;
use craft\models\FieldLayout;
use craft\records\VolumeFolder;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\CustomProperty;
use lenz\contentfield\json\scope\element\property\TransformsProperty;
use lenz\contentfield\json\scope\Project;
use lenz\contentfield\json\scope\StaticProperty;

/**
 * Class AssetStructure
 */
class AssetStructure extends Structure
{
  /**
   * @var Volume
   */
  public $volume;

  /**
   * @inheritDoc
   */
  static $elementType = Asset::class;


  /**
   * @inheritDoc
   */
  public function canExport($value): bool {
    return $value instanceof Asset && $value->volumeId == $this->volume->id;
  }

  /**
   * @return array
   */
  public function getContentSettingUids(): array {
    return [$this->volume->uid];
  }

  /**
   * @inheritDoc
   */
  public function getDefinitionBaseClass(): string {
    return 'contentfield.Asset';
  }

  /**
   * @inheritDoc
   */
  public function getFieldLayout(): FieldLayout {
    return $this->volume->getFieldLayout();
  }

  /**
   * @return array
   */
  public function getFilterFixtures(): array {
    return array_merge(parent::getFilterFixtures(), [
      'elementType' => 'asset',
      'volume' => $this->volume->handle,
      'volumeId' => $this->volume->id,
    ]);
  }

  /**
   * @inheritDoc
   */
  public function getLabel(): string {
    return $this->volume->name;
  }

  /**
   * @return ElementQuery
   */
  public function getQuery(): ElementQuery {
    return Asset::find()->volume($this->volume);
  }


  // Protected methods
  // -----------------

  /**
   * @inheritDoc
   */
  protected function loadStaticProperties(): array {
    return array_merge(parent::loadStaticProperties(), [
      new StaticProperty([
        'name' => 'volume',
        'structure' => $this,
        'definitionType' => self::literal($this->volume->handle),
        'value' => $this->volume->handle,
      ]),
      new CustomProperty([
        'name' => 'focalPoint',
        'structure' => $this,
        'definitionType' => 'contentfield.Point|null',
        'handler' => function(Asset $asset) { return $asset->focalPoint; }
      ]),
      new CustomProperty([
        'name' => 'height',
        'structure' => $this,
        'definitionType' => 'number',
        'handler' => function(Asset $asset) { return $asset->height; }
      ]),
      new CustomProperty([
        'name' => 'width',
        'structure' => $this,
        'definitionType' => 'number',
        'handler' => function(Asset $asset) { return $asset->width; }
      ]),
      new TransformsProperty([
        'name' => 'transforms',
        'structure' => $this,
      ]),
    ]);
  }


  // Static methods
  // --------------

  /**
   * @param Project $project
   * @return AssetStructure[]
   */
  static public function createStructures(Project $project): array {
    $result = [];

    foreach (Plugin::MODES as $mode) {
      $suffix = self::modeSuffix($mode);
      $assetGroup = $project->createUnion([
        'name' => self::join('AnyAsset', $suffix),
      ]);

      foreach (Craft::$app->volumes->getAllVolumes() as $volume) {
        $folder = VolumeFolder::findOne([
          'name' => $volume->name,
          'volumeId' => $volume->id,
        ]);

        $structure = new AssetStructure([
          'mode' => $mode,
          'name' => self::join($volume->handle, 'Asset', $suffix),
          'project' => $project,
          'volume' => $volume,
        ]);

        $assetGroup->structures[] = $structure;
        if ($mode === Plugin::MODE_REFERENCE) {
          $project->relationTypes['volume:' . $volume->uid] = $structure;
          $project->relationTypes['folder:' . $folder->uid] = $structure;
        }

        $result[] = $structure;
      }
    }

    return $result;
  }
}
