<?php

namespace lenz\contentfield\json\modifiers;

use lenz\contentfield\json\events\ProjectEvent;
use lenz\contentfield\json\scope\PropertyCollection;

/**
 * Class ElementModifier
 */
abstract class ElementModifier implements ModifierInterface
{
  /**
   * @var array
   */
  const FILTERS = [];


  /**
   * @param PropertyCollection $properties
   */
  abstract function __invoke(PropertyCollection $properties);

  /**
   * @param ProjectEvent $event
   */
  public function onCreateProject(ProjectEvent $event) {
    $event->modifyElement(static::FILTERS, $this);
  }
}
