<?php

namespace lenz\contentfield\json\modifiers;

use lenz\contentfield\json\events\ProjectEvent;
use lenz\contentfield\json\scope\PropertyCollection;

/**
 * Class ContentModifier
 */
abstract class ContentModifier implements ModifierInterface
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
    $event->modifyContent(static::FILTERS, $this);
  }
}
