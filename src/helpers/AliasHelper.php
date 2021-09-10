<?php

namespace lenz\contentfield\json\helpers;

use craft\helpers\FileHelper;
use lenz\contentfield\json\events\AliasEvent;
use yii\base\Event;

/**
 * Class AliasHelper
 */
class AliasHelper
{
  /**
   * @var string
   */
  public $separator = '/';

  /**
   * @var bool
   */
  private $_aliasesChanged = false;

  /**
   * @var array
   */
  private $_aliasPaths = [];

  /**
   * @var array
   */
  private $_aliases = [];

  /**
   * Triggered when an instance of the alias helper is created.
   */
  const EVENT_REGISTER_ALIASES = 'registerAliases';


  /**
   * AliasHelper constructor.
   */
  public function __construct() {
    Event::trigger(
      self::class, self::EVENT_REGISTER_ALIASES,
      new AliasEvent(['aliases' => $this])
    );
  }

  /**
   * @param string $alias
   * @param string $path
   */
  public function add(string $alias, string $path) {
    if (strncmp($alias, '@', 1)) {
      $alias = '@' . $alias;
    }

    $pos = strpos($alias, $this->separator);
    $root = $pos === false ? $alias : substr($alias, 0, $pos);
    if ($path !== null) {
      $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : $this->get($path);

      if (!isset($this->_aliases[$root])) {
        if ($pos === false) {
          $this->_aliases[$root] = $path;
        } else {
          $this->_aliases[$root] = [$alias => $path];
        }
      } elseif (is_string($this->_aliases[$root])) {
        if ($pos === false) {
          $this->_aliases[$root] = $path;
        } else {
          $this->_aliases[$root] = [
            $alias => $path,
            $root => $this->_aliases[$root],
          ];
        }
      } else {
        $this->_aliases[$root][$alias] = $path;
        krsort($this->_aliases[$root]);
      }
    } elseif (isset($this->_aliases[$root])) {
      if (is_array($this->_aliases[$root])) {
        unset($this->_aliases[$root][$alias]);
      } elseif ($pos === false) {
        unset($this->_aliases[$root]);
      }
    }

    $this->_aliasPaths[$alias] = FileHelper::normalizePath($path, $this->separator);
    $this->_aliasesChanged = true;
  }

  /**
   * @param string $alias
   * @return string|false
   */
  public function get(string $alias) {
    if (strpos($alias, '@') !== 0) {
      return $alias;
    }

    $pos = strpos($alias, $this->separator);
    $root = $pos === false ? $alias : substr($alias, 0, $pos);

    if (isset($this->_aliases[$root])) {
      if (is_string($this->_aliases[$root])) {
        return $pos === false
          ? $this->_aliases[$root]
          : $this->_aliases[$root] . substr($alias, $pos);
      }

      foreach ($this->_aliases[$root] as $name => $path) {
        if (strpos($alias . $this->separator, $name . $this->separator) === 0) {
          return $path . substr($alias, strlen($name));
        }
      }
    }

    return false;
  }

  /**
   * @return array
   */
  public function getAllAliases(): array {
    return $this->_aliasPaths;
  }

  /**
   * @param string $path
   * @param bool $force
   * @return string
   */
  public function stripAlias(string $path, bool $force = true): string {
    if ($force) {
      $path = $this->toAlias($path);
    }

    foreach (array_keys($this->_aliases) as $alias) {
      if (substr($path, 0, strlen($alias)) == $alias) {
        $path = substr($path, strlen($alias));
      }
    }

    return $path;
  }

  /**
   * @param string $path
   * @return string
   */
  public function toAlias(string $path): string {
    if ($this->_aliasesChanged) {
      $lengths = [];
      foreach ($this->_aliasPaths as $aliasPath) {
        $lengths[] = strlen($aliasPath);
      }

      $this->_aliasesChanged = false;
      array_multisort($lengths, SORT_DESC, SORT_NUMERIC, $this->_aliasPaths);
    }

    $path = FileHelper::normalizePath($path, $this->separator);
    foreach ($this->_aliasPaths as $alias => $aliasPath) {
      if (strpos($path . $this->separator, $aliasPath . $this->separator) === 0) {
        return $alias . substr($path, strlen($aliasPath));
      }
    }
    return $path;
  }
}
