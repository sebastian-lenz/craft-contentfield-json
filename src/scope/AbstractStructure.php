<?php

namespace lenz\contentfield\json\scope;

use lenz\contentfield\json\Plugin;
use Stringy\Stringy;
use yii\base\BaseObject;

/**
 * Class AbstractShape
 */
abstract class AbstractStructure extends BaseObject
{
  /**
   * @var
   */
  public $mode = Plugin::MODE_DEFAULT;

  /**
   * @var string
   */
  public $name;

  /**
   * @var Project
   */
  public $project;

  /**
   * @var callable[]
   */
  public $transforms = [];

  /**
   * @var callable[]
   */
  protected $_modifiers;

  /**
   * @var AbstractProperty[]
   */
  protected $_properties;


  /**
   * @param callable $modifier
   */
  public function addModifier(callable $modifier) {
    $this->_modifiers[] = $modifier;
  }

  /**
   * @param mixed $value
   * @return bool
   */
  public function canExport($value): bool {
    return false;
  }

  /**
   * @param DefinitionWriter $writer
   * @return void
   */
  public function definition(DefinitionWriter $writer) {
    $writer->beginInterfaceScope($this->name, $this->getDefinitionBaseClass());

    foreach ($this->getProperties() as $property) {
      if ($property->isEnabled) {
        $property->definition($writer);
      }
    }

    $writer->endScope();
  }

  /**
   * @param mixed $source
   * @param State $state
   * @return object
   */
  public function export($source, State $state): object {
    $target = (object)[];
    foreach ($this->getProperties() as $property) {
      if ($property->isEnabled) {
        $property->export($target, $source, $state);
      }
    }

    foreach ($this->transforms as $transform) {
      $transform($target, $source, $state);
    }

    return $target;
  }

  /**
   * @return string
   */
  public function getDefinitionBaseClass(): string {
    return '';
  }

  /**
   * @return array
   */
  public function getFilterFixtures(): array {
    return [
      'mode' => $this->mode,
    ];
  }

  /**
   * @return AbstractProperty[]
   */
  public function getProperties(): array {
    if (!isset($this->_properties)) {
      $this->_properties = $this->loadProperties();
    }

    return $this->_properties;
  }

  /**
   * @param array $filters
   * @return bool
   */
  public function matchesFilters(array $filters): bool {
    $fixtures = $this->getFilterFixtures();
    foreach ($filters as $key => $value) {
      if (!array_key_exists($key, $fixtures)) {
        return false;
      }

      $fixture = $fixtures[$key];
      if (is_callable($fixture)) {
        if (!$fixture($value)) {
          return false;
        }
      } else {
        if (
          (is_array($value) && !in_array($fixture, $value)) ||
          (is_scalar($value) && $value != $fixture)
        ) {
          return false;
        }
      }
    }

    return true;
  }
  // Protected methods
  // -----------------

  /**
   * @param AbstractProperty[] $properties
   * @return AbstractProperty[]
   */
  protected function applyModifiers(array $properties): array {
    if (!isset($this->_modifiers)) {
      return $properties;
    }

    $collection = new PropertyCollection($this, $properties);
    foreach ($this->_modifiers as $modifier) {
      $modifier($collection);
    }

    return $collection->getProperties();
  }

  /**
   * @return AbstractProperty[]
   */
  protected function loadProperties(): array {
    return [];
  }


  // Static methods
  // --------------

  /**
   * @return string
   */
  static function join(): string {
    return (new Stringy(
      implode(('-'), array_unique(func_get_args()))
    ))->upperCamelize();
  }

  /**
   * @param string $value
   * @return string
   */
  static function literal(string $value): string {
    return "'" . $value . "'";
  }

  /**
   * @param string $mode
   * @return string
   */
  static function modeSuffix(string $mode): string {
    return $mode === Plugin::MODE_DEFAULT ? '' : $mode;
  }
}
