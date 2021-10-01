<?php

namespace lenz\contentfield\json\scope;

use Craft;
use craft\base\ElementInterface;
use craft\models\CategoryGroup;
use craft\models\EntryType;
use craft\models\Section;

/**
 * Class Dependencies
 */
class State
{
  /**
   * @var array
   */
  public $metaData = [];

  /**
   * @var bool
   */
  public $useCache = true;

  /**
   * @var string[]
   */
  private $_dependencies = [];

  /**
   * @var string[]
   */
  private $_requirements = [];

  /**
   * @var array|null
   */
  private $_transforms = null;


  /**
   * @return $this|State
   */
  public function dependsOnAny(): State {
    return $this->dependsOn('*');
  }

  /**
   * @param ElementInterface|string $value
   * @return $this|State
   */
  public function dependsOnElement($value): State {
    if ($value instanceof ElementInterface) {
      $value = $value->uid;
    }

    return $this->dependsOn('element:' . $value);
  }

  /**
   * @param CategoryGroup|string $value
   * @return $this|State
   */
  public function dependsOnGroup($value): State {
    if ($value instanceof CategoryGroup) {
      $value = $value->handle;
    }

    return $this->dependsOn('group:' . $value);
  }

  /**
   * @param EntryType|string $value
   * @return $this|State
   */
  public function dependsOnType($value): State {
    if ($value instanceof EntryType) {
      $value = $value->handle;
    }

    return $this->dependsOn('type:' . $value);
  }

  /**
   * @param Section|string $value
   * @return $this|State
   */
  public function dependsOnSection($value): State {
    if ($value instanceof Section) {
      $value = $value->handle;
    }

    return $this->dependsOn('section:' . $value);
  }

  /**
   * @return string
   */
  public function getCacheId(): string {
    return is_null($this->_transforms)
      ? ''
      : implode(';', $this->_transforms);
  }

  /**
   * @return string[]
   */
  public function getDependencies(): array {
    return $this->_dependencies;
  }

  /**
   * @return string[]
   */
  public function getRequirements(): array {
    return $this->_requirements;
  }

  /**
   * @return array|null
   */
  public function getTransforms(): ?array {
    return $this->_transforms;
  }

  /**
   * @param string $value
   * @return $this|State
   */
  public function requires(string $value): State {
    $this->_requirements[] = $value;
    return $this;
  }

  /**
   * @param array|null $transforms
   * @param callable $callback
   * @return mixed
   */
  public function withTransforms(?array $transforms, callable $callback) {
    $lastTransforms = $this->_transforms;
    $this->_transforms = $transforms;

    $result = $callback();

    $this->_transforms = $lastTransforms;
    return $result;
  }


  // Private methods
  // ---------------

  /**
   * @param string $token
   * @return $this|State
   */
  private function dependsOn(string $token): State {
    if (!in_array($token, $this->_dependencies)) {
      $this->_dependencies[] = $token;
    }

    return $this;
  }
}
