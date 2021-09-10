<?php

namespace lenz\contentfield\json\scope;

/**
 * Class StaticProperty
 */
class StaticProperty extends AbstractProperty
{
  /**
   * @var mixed
   */
  public $value;


  /**
   * @param object $target
   * @param mixed $source
   * @param State $state
   */
  public function export(object $target, $source, State $state) {
    $target->{$this->name} = $this->value;
  }
}
