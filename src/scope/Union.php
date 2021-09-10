<?php

namespace lenz\contentfield\json\scope;

use yii\base\BaseObject;

/**
 * Class Union
 */
class Union extends BaseObject
{
  /**
   * @var string
   */
  public $name;

  /**
   * @var AbstractStructure[]
   */
  public $structures = [];


  /**
   * @param DefinitionWriter $writer
   */
  public function definition(DefinitionWriter $writer) {
    $type = implode('|', array_map(function(AbstractStructure $structure) {
      return $structure->name;
    }, $this->structures));

    if (empty($type)) {
      $type = 'unknown';
    }

    $writer->push("type {$this->name} = {$type};");
  }
}
