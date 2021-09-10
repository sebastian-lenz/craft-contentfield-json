<?php

namespace lenz\contentfield\json\events;

use lenz\contentfield\json\scope\Project;
use yii\base\Event;

/**
 * Class ProjectEvent
 */
class ProjectEvent extends Event
{
  /**
   * @var Project
   */
  public $project;


  /**
   * ProjectEvent constructor.
   * @param Project $project
   */
  public function __construct(Project $project) {
    parent::__construct();
    $this->project = $project;
  }

  /**
   * @param string $definitionFile
   * @return $this
   */
  public function addDefinitionFile(string $definitionFile): ProjectEvent {
    $this->project->definitionFiles[] = $definitionFile;
    return $this;
  }

  /**
   * @param array $filters
   * @param callable $modifier
   * @return $this
   */
  public function modifyContent(array $filters, callable $modifier): ProjectEvent {
    foreach ($this->project->getContentStructures() as $structure) {
      if ($structure->matchesFilters($filters)) {
        $structure->addModifier($modifier);
      }
    }

    return $this;
  }

  /**
   * @param array $filters
   * @param callable $modifier
   * @return $this
   */
  public function modifyElement(array $filters, callable $modifier): ProjectEvent {
    foreach ($this->project->getElementStructures() as $structure) {
      if ($structure->matchesFilters($filters)) {
        $structure->addModifier($modifier);
      }
    }

    return $this;
  }
}
