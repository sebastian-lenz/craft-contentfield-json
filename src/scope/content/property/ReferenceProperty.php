<?php

namespace lenz\contentfield\json\scope\content\property;

use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use Exception;
use lenz\contentfield\helpers\ReferenceMap;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\content\Property;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\fields\ReferenceField;
use lenz\contentfield\models\values\ReferenceValue;
use Throwable;

/**
 * Class ReferenceProperty
 *
 * @property ReferenceField $field
 */
class ReferenceProperty extends Property
{
  /**
   * @inheritDoc
   */
  const TARGETS = [ReferenceField::class];


  /**
   * @inheritDoc
   * @throws Exception
   */
  public function exportValue($value, State $state) {
    if (!($value instanceof ReferenceValue)) {
      return null;
    }

    $references = $value->getReferences();
    foreach ($references as $reference) {
      $state->dependsOnElement($reference->uid);
    }

    $result = $state->withTransforms(
      $this->getTransforms(),
      function() use ($references, $state) {
        return Plugin::toJson($references, Plugin::MODE_REFERENCE, $state);
      }
    );

    if ($this->field->limit === 1) {
      return count($result) ? reset($result) : null;
    } else {
      return $result;
    }
  }

  /**
   * @return string
   * @throws Throwable
   */
  public function getDefinitionType(): string {
    return $this->field->limit === 1
      ? $this->getRelationType() . ' | null'
      : "Array<{$this->getRelationType()}>";
  }

  /**
   * @return array|null
   */
  public function getTransforms(): ?array {
    $transforms = $this->field->withTransforms;
    return is_null($transforms) ? null : ReferenceMap::splitWithValue($transforms);
  }


  // Protected methods
  // -----------------

  /**
   * @return string
   */
  protected function getAllRelationType(): string {
    switch ($this->field->elementType) {
      case Asset::class:
        return 'AnyAssetReference';
      case Category::class:
        return 'AnyCategoryReference';
      case Entry::class:
        return 'AnyEntryReference';
    }

    return 'contentfield.Element';
  }

  /**
   * @return string
   */
  protected function getRelationType(): string {
    return $this->structure->project->getRelationType(
      $this->getAllRelationType(),
      $this->field->sources
    );
  }
}
