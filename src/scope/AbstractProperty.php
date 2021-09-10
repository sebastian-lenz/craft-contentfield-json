<?php

namespace lenz\contentfield\json\scope;

use yii\base\BaseObject;

/**
 * Class AbstractField
 */
abstract class AbstractProperty extends BaseObject
{
  /**
   * @var string
   */
  public $definitionType = 'any';

  /**
   * @var bool
   */
  public $isEnabled = true;

  /**
   * @var string
   */
  public $name;

  /**
   * @var AbstractStructure
   */
  public $structure;


  /**
   * @param DefinitionWriter $writer
   */
  public function definition(DefinitionWriter $writer) {
    $writer->push("{$this->name}: {$this->getDefinitionType()};");
  }

  /**
   * @param object $target
   * @param mixed $source
   * @param State $state
   */
  public function export(object $target, $source, State $state) { }

  /**
   * @return string
   */
  public function getDefinitionType(): string {
    return $this->definitionType;
  }
}
