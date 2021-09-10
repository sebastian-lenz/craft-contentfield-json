<?php

namespace lenz\contentfield\json\scope\element\property;

use lenz\contentfield\fields\ContentField;
use lenz\contentfield\json\Plugin;
use lenz\contentfield\json\scope\AbstractStructure;
use lenz\contentfield\json\scope\content\Structure;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\Content as ContentModel;
use lenz\contentfield\json\scope\element\Property;
use Throwable;

/**
 * Class ContentProperty
 *
 * @property ContentField $field
 */
class ContentProperty extends Property
{
  /**
   * @inheritDoc
   */
  const TARGETS = [ContentField::class];


  /**
   * @inheritDoc
   * @throws Throwable
   */
  public function getDefinitionType(): string {
    $qualifiers = $this->getRootSchemas();

    $structures = array_filter(
      $this->structure->project->getContentStructures(),
      function($structure) use ($qualifiers) {
        return is_null($qualifiers)
          ? $structure->schema->rootSchema
          : $structure->schema->matchesQualifier($qualifiers);
      }
    );

    return count($structures)
      ? implode('|', array_map(function($structure) {
          return $structure->name;
        }, $structures))
      : 'unknown';
  }

  /**
   * @inheritDoc
   */
  public function exportValue($value, $source, State $state) {
    return $value instanceof ContentModel
      ? Plugin::toJson($value->getModel(), Plugin::MODE_DEFAULT, $state)
      : null;
  }


  // Protected methods
  // -----------------

  /**
   * @return null|array
   */
  protected function getRootSchemas(): ?array {
    $uids = $this->structure->getContentSettingUids();
    $rootSchemasByUsage = $this->field->rootSchemasByUsage;

    foreach ($uids as $uid) {
      if (array_key_exists($uid, $rootSchemasByUsage)) {
        return $rootSchemasByUsage[$uid];
      }
    }

    return $this->field->rootSchemas;
  }
}
