<?php

namespace lenz\contentfield\json\scope;

use craft\helpers\ArrayHelper;
use lenz\contentfield\json\events\ProjectEvent;
use Throwable;
use yii\base\BaseObject;
use yii\base\Component;

/**
 * Class PropertyCollection
 */
class PropertyCollection
{
  /**
   * @var AbstractProperty[]
   */
  private $_properties;

  /**
   * @var AbstractStructure
   */
  private $_structure;


  /**
   * PropertyCollection constructor.
   *
   * @param AbstractStructure $structure
   * @param AbstractProperty[] $properties
   */
  public function __construct(AbstractStructure $structure, array $properties) {
    $this->_structure = $structure;
    $this->_properties = array_values($properties);
  }

  /**
   * @param string $name
   * @param string $definition
   * @param callable $handler
   * @return PropertyCollection
   */
  public function add(string $name, string $definition, callable $handler): PropertyCollection {
    $property = new CustomProperty([
      'definitionType' => $definition,
      'handler' => $handler,
      'name' => $name,
      'structure' => $this->_structure,
    ]);

    $this->delete($name);
    $this->_properties[] = $property;
    return $this;
  }

  /**
   * @param string $name
   * @return $this
   */
  public function delete(string $name): PropertyCollection {
    for ($index = 0; $index < count($this->_properties); $index++) {
      if ($this->_properties[$index]->name == $name) {
        array_splice($this->_properties, $index, 1);
        break;
      }
    }

    return $this;
  }

  /**
   * @param string $name
   * @return $this
   */
  public function disable(string $name): PropertyCollection {
    return $this->toggle($name, false);
  }

  /**
   * @param string $name
   * @return $this
   */
  public function enable(string $name): PropertyCollection {
    return $this->toggle($name, true);
  }

  /**
   * @param string $name
   * @return AbstractProperty|null
   */
  public function getProperty(string $name): ?AbstractProperty {
    foreach ($this->_properties as $property) {
      if ($property->name == $name) {
        return $property;
      }
    }

    return null;
  }

  /**
   * @return AbstractProperty[]
   */
  public function getProperties(): array {
    return $this->_properties;
  }

  /**
   * @param string $name
   * @return bool
   */
  public function hasProperty(string $name): bool {
    return !is_null($this->getProperty($name));
  }

  /**
   * @param string $oldName
   * @param string $newName
   * @return $this
   */
  public function rename(string $oldName, string $newName): PropertyCollection {
    $property = $this->getProperty($oldName);
    if (!is_null($property)) {
      $this->delete($newName);
      $property->name = $newName;
    }

    return $this;
  }

  /**
   * @param string $name
   * @param callable $transform
   */
  public function transform(string $name, callable $transform) {
    $this->_structure->transforms[$name] = $transform;
  }

  /**
   * @param string $name
   * @param bool $isEnabled
   * @return $this
   */
  public function toggle(string $name, bool $isEnabled): PropertyCollection {
    $property = $this->getProperty($name);
    if ($property) {
      $property->isEnabled = $isEnabled;
    }

    return $this;
  }
}
