<?php

namespace lenz\contentfield\json;

use craft\base\ElementInterface;
use craft\base\Plugin as BasePlugin;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\values\InstanceValue;

/**
 * Class Plugin
 *
 * @property helpers\AliasHelper $alias
 * @property modifiers\ModifierManager $modifiers
 * @property scope\Project $project
 */
class Plugin extends BasePlugin
{
  /**
   * @var bool
   */
  static $ensureAssetTransforms = false;

  /**
   * Known structure modes.
   */
  const MODES = [self::MODE_DEFAULT, self::MODE_REFERENCE];
  const MODE_DEFAULT = 'default';
  const MODE_REFERENCE = 'reference';


  /**
   * @inheritDoc
   */
  public function init() {
    parent::init();

    $this->setComponents([
      'alias' => helpers\AliasHelper::class,
      'modifiers' => modifiers\ModifierManager::class,
      'project' => scope\Project::class,
    ]);
  }


  // Static methods
  // --------------

  /**
   * @param string $path
   * @return string
   */
  static public function stripAlias(string $path): string {
    return Plugin::getInstance()->alias->stripAlias($path);
  }

  /**
   * @param InstanceValue|InstanceValue[]|ElementInterface|ElementInterface[] $value
   * @param string $mode
   * @param State|null $state
   * @return object|array|null
   */
  static public function toJson($value, string $mode = self::MODE_DEFAULT, State $state = null) {
    if (is_array($value)) {
      $result = [];
      foreach ($value as $item) {
        $result[] = self::toJson($item, $mode, $state);
      }

      return $result;
    }

    return self::getInstance()->project->toJson($value, $mode, $state);
  }

  /**
   * @param string $path
   * @return string
   */
  static public function toAlias(string $path): string {
    return Plugin::getInstance()->alias->toAlias($path);
  }
}
