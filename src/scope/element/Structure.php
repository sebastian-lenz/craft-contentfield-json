<?php

namespace lenz\contentfield\json\scope\element;

use craft\base\Element;
use craft\base\Field;
use craft\elements\db\ElementQuery;
use craft\models\FieldLayout;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\AbstractProperty;
use lenz\contentfield\json\scope\AbstractStructure;
use lenz\contentfield\json\scope\CustomProperty;
use lenz\contentfield\json\scope\DefinitionWriter;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\json\scope\Project;
use lenz\contentfield\json\scope\PropertyCollection;
use yii\base\BaseObject;

/**
 * Class Structure
 *
 * @property Property[] $_properties
 */
abstract class Structure extends AbstractStructure
{
  /**
   * @var FieldLayout
   */
  public $fieldLayout;

  /**
   * @var string|Element
   */
  static $elementType = Element::class;


  /**
   * @inheritDoc
   */
  public function definition(DefinitionWriter $writer) {
    $label = $this->mode == Plugin::MODE_REFERENCE ? 'Reference ' : 'Element ';
    $label .= $this->getLabel();
    $writer->docComment([$label]);

    parent::definition($writer);
  }

  /**
   * @param mixed $source
   * @param State $state
   * @return object
   */
  public function export($source, State $state): object {
    $state->dependsOnElement($source);

    return parent::export($source, $state);
  }

  /**
   * @return array
   */
  public function getContentSettingUids(): array {
    return [];
  }

  /**
   * @return string
   */
  public function getLabel(): string {
    return static::$elementType;
  }


  // Abstract methods
  // ----------------

  /**
   * @return FieldLayout
   */
  abstract function getFieldLayout(): FieldLayout;

  /**
   * @return ElementQuery
   */
  abstract function getQuery(): ElementQuery;


  // Protected methods
  // -----------------

  /**
   * @inheritDoc
   */
  final protected function loadProperties(): array {
    $fields = array_map(function(Field $field) {
      return Property::create($this, $field);
    }, $this->getFieldLayout()->getFields());

    return $this->applyModifiers(
      array_merge($this->loadStaticProperties(), $fields)
    );
  }

  /**
   * @return AbstractProperty[]
   */
  protected function loadStaticProperties(): array {
    return [
      new CustomProperty([
        'name' => 'uid',
        'structure' => $this,
        'definitionType' => 'string',
        'handler' => function(Element $element, State $state) {
          if (array_key_exists('generatorUid', $state->metaData)) {
            return $state->metaData['generatorUid'];
          }

          return $element->uid;
        }
      ]),
      new CustomProperty([
        'name' => 'title',
        'structure' => $this,
        'definitionType' => 'string',
        'handler' => function(Element $element) {
          return $element->title;
        }
      ]),
      new CustomProperty([
        'name' => 'url',
        'structure' => $this,
        'definitionType' => 'string|null',
        'handler' => function(Element $element, State $state) {
          if (array_key_exists('generatorUrl', $state->metaData)) {
            return $state->metaData['generatorUrl'];
          }

          $url = $element->getUrl();
          return $url ? Plugin::toAlias($url) : null;
        }
      ]),
    ];
  }


  // Static methods
  // --------------

  /**
   * @param Project $project
   * @return Structure[]
   */
  static public function createStructures(Project $project): array {
    return array_merge(
      AssetStructure::createStructures($project),
      CategoryStructure::createStructures($project),
      EntryStructure::createStructures($project),
      GlobalSetStructure::createStructures($project),
      MatrixBlockStructure::createStructures($project)
    );
  }
}
