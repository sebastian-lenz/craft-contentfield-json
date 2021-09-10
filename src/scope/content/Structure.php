<?php

namespace lenz\contentfield\json\scope\content;

use lenz\contentfield\json\scope\AbstractProperty;
use lenz\contentfield\json\scope\AbstractStructure;
use lenz\contentfield\json\scope\CustomProperty;
use lenz\contentfield\json\scope\DefinitionWriter;
use lenz\contentfield\json\scope\Project;
use lenz\contentfield\json\scope\PropertyCollection;
use lenz\contentfield\json\scope\StaticProperty;
use lenz\contentfield\models\fields\AbstractField;
use lenz\contentfield\models\schemas\AbstractSchema;
use lenz\contentfield\models\schemas\AbstractSchemaContainer;
use lenz\contentfield\models\values\InstanceValue;
use lenz\contentfield\Plugin;
use Stringy\Stringy;

/**
 * Class Structure
 *
 * @property Property[] $_properties
 */
class Structure extends AbstractStructure
{
  /**
   * @var AbstractSchema
   */
  public $schema;


  /**
   * @inheritDoc
   */
  public function canExport($value): bool {
    return (
      $value instanceof InstanceValue &&
      $value->getSchema()->qualifier == $this->schema->qualifier
    );
  }

  /**
   * @inheritDoc
   */
  function definition(DefinitionWriter $writer) {
    $writer->docComment(['Schema ' . $this->schema->getLabel()]);

    parent::definition($writer);
  }

  /**
   * @inheritDoc
   */
  public function getDefinitionBaseClass(): string {
    return 'contentfield.Instance';
  }

  /**
   * @return array
   */
  public function getFilterFixtures(): array {
    return array_merge(parent::getFilterFixtures(), [
      'qualifier' => function($value) {
        return $this->schema->matchesQualifier($value);
      }
    ]);
  }


  // Protected methods
  // -----------------

  /**
   * @inheritDoc
   */
  final protected function loadProperties(): array {
    $fields = array_map(function(AbstractField $field) {
      return Property::create($this, $field);
    }, $this->schema->fields);

    return $this->applyModifiers(
      array_merge($this->loadStaticProperties(), $fields)
    );
  }

  /**
   * @return AbstractProperty[]
   */
  protected function loadStaticProperties(): array {
    return [
      new StaticProperty([
        'name' => 'type',
        'structure' => $this,
        'definitionType' => self::literal($this->schema->qualifier),
        'value' => $this->schema->qualifier,
      ]),
      new CustomProperty([
        'name' => 'uid',
        'structure' => $this,
        'definitionType' => 'string',
        'handler' => function(InstanceValue $instance) {
          return $instance->getUuid();
        },
      ]),
      new property\AnchorProperty([
        'name' => 'anchor',
        'structure' => $this,
      ])
    ];
  }


  // Static methods
  // --------------

  /**
   * @param Project $project
   * @return Structure[]
   */
  static public function createStructures(Project $project): array {
    $schemas = Plugin::getInstance()->schemas->getAllSchemas();
    $groups = [];
    $result = [];

    foreach ($schemas as $schema) {
      $schemaName = self::getSchemaName($schema);
      $result[] = $structure = new Structure([
        'name' => $schemaName,
        'project' => $project,
        'schema' => $schema,
      ]);

      $parts = self::getSchemaNameParts($schema);
      for ($index = 0; $index < count($parts); $index += max(1, count($parts) - 1)) {
        $groupName = self::toUpperCamel('Any', array_slice($parts, 0, $index), 'Instance');
        if (!array_key_exists($groupName, $groups)) {
          $groups[$groupName] = $project->createUnion([
            'name' => $groupName,
          ]);
        }

        $groups[$groupName]->structures[] = $structure;
      }

      if ($schema instanceof AbstractSchemaContainer) {
        foreach ($schema->getLocalStructures() as $localStructure) {
          $result[] = new Structure([
            'name' => $schemaName . '_' . self::toUpperCamel($localStructure->getName()),
            'project' => $project,
            'schema' => $localStructure,
          ]);
        }
      }
    }

    return $result;
  }

  /**
   * @param AbstractSchema $schema
   * @return string
   */
  public static function getSchemaName(AbstractSchema $schema): string {
    return self::toUpperCamel(self::getSchemaNameParts($schema), 'Instance');
  }

  /**
   * @param AbstractSchema $schema
   * @return array
   */
  public static function getSchemaNameParts(AbstractSchema $schema): array {
    return array_filter(
      explode('/', $schema->getName()),
      function($part) {
        return $part[0] != '_';
      }
    );
  }

  /**
   * @return string
   */
  public static function toUpperCamel(): string {
    $parts = [];
    foreach (func_get_args() as $arg) {
      if (is_string($arg)) {
        $parts[] = $arg;
      } else {
        $parts = array_merge($parts, $arg);
      }
    }

    return (new Stringy(implode('-', $parts)))->upperCamelize();
  }
}
