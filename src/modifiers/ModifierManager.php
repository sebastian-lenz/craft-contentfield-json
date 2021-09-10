<?php

namespace lenz\contentfield\json\modifiers;

use lenz\contentfield\json\events\ProjectEvent;
use lenz\contentfield\json\helpers\AbstractManager;

/**
 * Class ModifierManager
 *
 * @method ModifierInterface|null getInstance(string $name)
 * @method ModifierInterface[] getInstances()
 */
class ModifierManager extends AbstractManager
{
  /**
   * @inheritDoc
   */
  CONST SEGMENT = 'modifiers';

  /**
   * @inheritDoc
   */
  const SUFFIX = 'modifier';

  /**
   * @inheritDoc
   */
  const ITEM_CLASS = ModifierInterface::class;


  /**
   * @param ProjectEvent $event
   */
  public function onCreateProject(ProjectEvent $event) {
    foreach ($this->getInstances() as $instance) {
      $instance->onCreateProject($event);
    }
  }
}
