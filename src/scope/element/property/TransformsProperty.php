<?php

namespace lenz\contentfield\json\scope\element\property;

use craft\elements\Asset;
use craft\errors\AssetTransformException;
use craft\models\AssetTransformIndex;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\AbstractProperty;
use lenz\contentfield\json\scope\State;
use yii\base\InvalidConfigException;

/**
 * Class TransformsProperty
 */
class TransformsProperty extends AbstractProperty
{
  /**
   * @return string
   */
  public function getDefinitionType(): string {
    return 'contentfield.AssetTransformMap|undefined';
  }

  /**
   * @inheritDoc
   */
  public function export(object $target, $source, State $state) {
    $transforms = $state->getTransforms();
    if (!($source instanceof Asset) || empty($transforms)) {
      return;
    }

    $result = (object)[];
    foreach ($transforms as $transform) {
      $this->pushTransformInfo($result, $source, $transform);
    }

    $target->{$this->name} = $result;
  }


  // Private methods
  // ---------------

  /**
   * @param Asset $source
   * @param $transform
   * @param AssetTransformIndex|null $transformIndex
   * @return object
   * @throws AssetTransformException
   * @throws InvalidConfigException
   */
  private function createTransformInfo(Asset $source, $transform, ?AssetTransformIndex $transformIndex = null): object {
    static $dimensions;
    if (!isset($dimensions)) {
      $dimensions = new \ReflectionMethod(Asset::class, '_dimensions');
      $dimensions->setAccessible(true);
    }

    $url = $source->getUrl($transform, Plugin::$ensureAssetTransforms);
    $size = $dimensions->invoke($source, $transform);

    return (object)[
      'height' => $size[1],
      'url' => Plugin::toAlias($url),
      'width' => $size[0],
    ];
  }

  /**
   * @param object $result
   * @param Asset $source
   * @param string|array $transform
   * @throws AssetTransformException
   * @throws InvalidConfigException
   */
  private function pushTransformInfo(object $result, Asset $source, $transform) {
    static $transformNames = [];
    $transformIndex = null;

    if (is_string($transform)) {
      $name = $transform;
    } else {
      $hash = md5(serialize($transform));
      if (!array_key_exists($hash, $transformNames)) {
        $transformIndex = \Craft::$app->assetTransforms->getTransformIndex($source, $transform);
        $transformNames[$hash] = $transformIndex->location;
      }

      $name = $transformNames[$hash];
    }

    $result->$name = $this->createTransformInfo($source, $transform, $transformIndex);
  }
}
