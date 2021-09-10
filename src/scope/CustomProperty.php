<?php

namespace lenz\contentfield\json\scope;

/**
 * Class CustomProperty
 */
class CustomProperty extends AbstractProperty
{
  /**
   * @var callable
   */
  public $handler;


  /**
   * @param object $target
   * @param mixed $source
   * @param State $state
   */
  public function export(object $target, $source, State $state) {
    $handler = $this->handler;
    $target->{$this->name} = $handler($source, $state);
  }
}
