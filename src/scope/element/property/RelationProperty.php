<?php

namespace lenz\contentfield\json\scope\element\property;

use craft\elements\db\ElementQuery;
use craft\fields\Assets;
use craft\fields\BaseRelationField;
use craft\fields\Categories;
use craft\fields\Entries;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\AbstractStructure;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\json\scope\element\Property;

/**
 * Class RelationProperty
 *
 * @property BaseRelationField $field
 */
class RelationProperty extends Property
{
  /**
   * @inheritDoc
   */
  const TARGETS = [BaseRelationField::class];


  /**
   * @return string
   */
  public function getDefinitionType(): string {
    return 'Array<' . $this->getRelationType() . '>';
  }

  /**
   * @inheritDoc
   */
  public function exportValue($value, $source, State $state) {
    if ($value instanceof ElementQuery) {
      $value = $value->all();
      $source->setEagerLoadedElements($this->field->handle, $value);
    }

    if (!is_array($value)) {
      return null;
    }

    $result = Plugin::toJson($value, Plugin::MODE_REFERENCE, $state);
    foreach ($value as $reference) {
      $state->dependsOnElement($reference->uid);
    }

    return $result;
  }


  // Protected methods
  // -----------------

  /**
   * @return string
   */
  protected function getAllRelationType(): string {
    if ($this->field instanceof Assets) {
      return 'AnyAssetReference';
    } elseif ($this->field instanceof Categories) {
      return 'AnyCategoryReference';
    } elseif ($this->field instanceof Entries) {
      return 'AnyEntryReference';
    }

    return 'contentfield.Element';
  }

  /**
   * @return string
   */
  protected function getRelationType(): string {
    $allType = $this->getAllRelationType();
    $sources = $this->field->allowMultipleSources
      ? $this->field->sources
      : $this->field->source;

    return $this->structure->project->getRelationType($allType, $sources);
  }
}
