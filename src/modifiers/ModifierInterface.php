<?php

namespace lenz\contentfield\json\modifiers;

use lenz\contentfield\json\events\ProjectEvent;

/**
 * Interface ModifierInterface
 */
interface ModifierInterface
{
  /**
   * @param ProjectEvent $event
   */
  public function onCreateProject(ProjectEvent $event);
}
