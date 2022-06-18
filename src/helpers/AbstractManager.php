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
    $result = [];
    $segment = static::SEGMENT;
    $options = [
      'itemClass' => static::ITEM_CLASS,
      'segment' => static::SEGMENT,
      'suffix' => static::SUFFIX,
    ];

    /** @var Module $module */
    foreach (Craft::$app->modules as $name => $module) {
      $className = is_object($module) ? get_class($module) : $module;
      $namespace = substr($className, 0, strrpos($className, '\\'));
      if (empty($namespace)) {
        continue;
      }

      $basePath = Craft::parseEnv("@$name/json/$segment");
      if (!file_exists($basePath)) {
        continue;
      }

      self::crawlClasses($result, $basePath, array_merge($options, [
        'namespace' => $namespace,
      ]));
    }

    return $result;
  }


  // Static methods
  // --------------

  /**
   * @param array $result
   * @param string $basePath
   * @param array{itemClass: string, namespace: string, segment: string, suffix:string} $options
   */
  static public function crawlClasses(array &$result, string $basePath, array $options): void {
    if (!file_exists($basePath)) {
      return;
    }

    foreach (scandir($basePath) as $fileName) {
      if ($fileName[0] === '.') {
        continue;
      }

      $path = $basePath . DIRECTORY_SEPARATOR . $fileName;
      if (is_dir($path)) {
        self::crawlClasses($result, $path, array_merge($options, [
          'segment' => $options['segment'] . '\\' . $fileName,
        ]));
      }
      else if (substr($fileName, -4) === '.php') {
        $name = substr($fileName, 0, strlen($fileName) - 4);
        $className = implode('\\', [
          $options['namespace'], 'json', $options['segment'], $name
        ]);

        if (
          !class_exists($className) ||
          !is_subclass_of($className, $options['itemClass'], true)
        ) {
          continue;
        }

        $name = strtolower($name);
        self::stripSuffix($name, $options['suffix']);
        $result[$name] = $className;
      }
    }
  }

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
