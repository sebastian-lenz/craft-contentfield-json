<?php

namespace lenz\contentfield\json\helpers;

use Craft;
use stdClass;
use yii\base\Module;
use yii\helpers\StringHelper;

/**
 * Class AbstractManager
 */
abstract class AbstractManager
{
  /**
   * @var array
   */
  private $_classes;

  /**
   * @var array
   */
  private $_instances = [];

  /**
   * @var string
   */
  CONST SEGMENT = '';

  /**
   * @var string
   */
  CONST SUFFIX = '';

  /**
   * @var string
   */
  const ITEM_CLASS = stdClass::class;


  /**
   * @return array
   */
  public function getClasses(): array {
    if (!isset($this->_classes)) {
      $this->_classes = $this->findClasses();
    }

    return $this->_classes;
  }

  /**
   * @param string $name
   * @return object|null
   */
  public function getInstance(string $name): ?object {
    $name = strtolower($name);
    if (array_key_exists($name, $this->_instances)) {
      return $this->_instances[$name];
    }

    $classes = $this->getClasses();
    if (array_key_exists($name, $classes)) {
      $class = $classes[$name];
      $instance = new $class();
      $this->_instances[$name] = $instance;
      return $instance;
    }

    return null;
  }

  /**
   * @return object[]
   */
  public function getInstances(): array {
    foreach ($this->getClasses() as $name => $class) {
      if (!array_key_exists($name, $this->_instances)) {
        $this->_instances[$name] = new $class();
      }
    }

    return $this->_instances;
  }

  /**
   * @return void
   */
  public function reset() {
    $this->_instances = [];
  }


  // Protected methods
  // -----------------

  /**
   * @return array
   */
  protected function findClasses(): array {
    $itemClass = static::ITEM_CLASS;
    $segment = static::SEGMENT;
    $suffix = static::SUFFIX;
    $result = [];

    /** @var Module $module */
    foreach (Craft::$app->modules as $name => $module) {
      $className = get_class($module);
      $namespace = substr($className, 0, strrpos($className, '\\'));
      if (empty($namespace)) {
        continue;
      }

      $path = Craft::parseEnv("@$name/json/$segment");
      if (!file_exists($path)) {
        continue;
      }

      foreach (scandir($path) as $fileName) {
        if (substr($fileName, -4) !== '.php') continue;
        $name = substr($fileName, 0, strlen($fileName) - 4);
        $className = "$namespace\\json\\$segment\\$name";

        if (
          !class_exists($className) ||
          !is_subclass_of($className, $itemClass, true)
        ) {
          continue;
        }

        $name = strtolower($name);
        self::stripSuffix($name, $suffix);
        $result[$name] = $className;
      }
    }

    return $result;
  }


  // Static methods
  // --------------

  /**
   * @param string $path
   * @param string $suffix
   * @return bool
   */
  static public function stripSuffix(string &$path, string $suffix): bool {
    $suffixLen = strlen($suffix);
    if (substr($path, $suffixLen * -1) === $suffix) {
      $path = substr($path, 0, strlen($path) - $suffixLen);
      return true;
    }

    return false;
  }
}
