<?php

namespace lenz\contentfield\json\scope;

use craft\base\ElementInterface;
use lenz\contentfield\json\events\ProjectEvent;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\models\values\InstanceValue;
use Throwable;
use yii\base\Component;

/**
 * Class Project
 */
class Project extends Component
{
  /**
   * @var string[]
   */
  public $definitionFiles;

  /**
   * @var array|AbstractStructure[]|Union[]
   */
  public $relationTypes = [];

  /**
   * @var string
   */
  public $typesNamespace = 'hft';

  /**
   * @var Union[]
   */
  public $unions = [];

  /**
   * @var array
   */
  private $_cache = [];

  /**
   * @var content\Structure[]
   */
  private $_contentStructures;

  /**
   * @var element\Structure[]
   */
  private $_elementStructures;

  /**
   * Triggered when a project is created.
   */
  const EVENT_CREATE_PROJECT = 'createProject';


  /**
   * Project constructor.
   */
  public function __construct() {
    parent::__construct();

    $this->_contentStructures = content\Structure::createStructures($this);
    $this->_elementStructures = element\Structure::createStructures($this);
    $this->definitionFiles = [
      dirname(__DIR__, 2) . '/types/base.d.ts'
    ];

    $event = new ProjectEvent($this);
    $plugin = Plugin::getInstance();
    $plugin->modifiers->onCreateProject($event);

    $this->trigger(self::EVENT_CREATE_PROJECT, $event);
  }

  /**
   * @param array $filters
   * @return AbstractStructure[]
   */
  public function findStructures(array $filters): array {
    return array_filter($this->getStructures(), function(AbstractStructure $structure) use ($filters) {
      return $structure->matchesFilters($filters);
    });
  }

  /**
   * @param string|string[] $specs
   * @return content\Structure[]
   * @throws Throwable
   */
  public function findSchemas($specs): array {
    $result = [];
    foreach ($this->_contentStructures as $structure) {
      if ($structure->schema->matchesQualifier($specs)) {
        $result[] = $structure;
      }
    }

    return $result;
  }

  /**
   * @return content\Structure[]
   */
  public function getContentStructures(): array {
    return $this->_contentStructures;
  }

  /**
   * @return element\Structure[]
   */
  public function getElementStructures(): array {
    return $this->_elementStructures;
  }

  /**
   * @param string $allType
   * @param string|string[] $sources
   * @return string
   */
  public function getRelationType(string $allType, $sources): string {
    $mappings = $this->relationTypes;
    $sources = is_array($sources) ? $sources : [$sources];
    $result = [];

    foreach ($sources as $source) {
      if ($source == '*' || !array_key_exists($source, $mappings)) {
        return $allType;
      }

      $result[] = $mappings[$source]->name;
    }

    return empty($result)
      ? $allType
      : implode('|', $result);
  }

  /**
   * @return AbstractStructure[]
   */
  public function getStructures(): array {
    return array_merge(
      $this->_contentStructures,
      $this->_elementStructures
    );
  }

  /**
   * @return string
   */
  public function toDefinitions(): string {
    $writer = new DefinitionWriter();
    foreach ($this->definitionFiles as $definitionFile) {
      $writer->pushFile($definitionFile);
    }

    $writer->beginNamespaceScope($this->typesNamespace);

    foreach ($this->unions as $union) {
      $union->definition($writer);
    }

    $writer->push('');

    foreach ($this->getStructures() as $structure) {
      $structure->definition($writer);
    }

    $writer->endScope();
    return (string)$writer;
  }

  /**
   * @param InstanceValue|ElementInterface $value
   * @param string $mode
   * @param State|null $state
   * @return object|null
   */
  public function toJson($value, string $mode = Plugin::MODE_DEFAULT, State $state = null): ?object {
    if ($value instanceof InstanceValue) {
      $structures = $this->_contentStructures;
      $uid = $value->getUuid() . ';' . $value->getElement()->siteId;
    } elseif ($value instanceof ElementInterface) {
      $structures = $this->_elementStructures;
      $uid = $value->uid . ';' . $value->siteId;
    } else {
      return null;
    }

    if (is_null($state)) {
      $state = new State();
    }

    $uid .= ';' . $state->getCacheId();
    if ($state->useCache && array_key_exists($uid, $this->_cache)) {
      return $this->_cache[$uid];
    }

    $result = null;
    foreach ($structures as $structure) {
      if ($structure->mode == $mode && $structure->canExport($value)) {
        $result = $structure->export($value, $state);
        break;
      }
    }

    if ($state->useCache) {
      $this->_cache[$uid] = $result;
    }

    return $result;
  }

  /**
   * @param array $config
   * @return Union
   */
  public function createUnion(array $config): Union {
    $group = new Union($config);
    $this->unions[] = $group;
    return $group;
  }
}
