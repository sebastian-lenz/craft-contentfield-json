<?php

namespace lenz\contentfield\json\scope\content\property;

use lenz\contentfield\json\scope\AbstractProperty;
use lenz\contentfield\json\scope\content\Property;
use lenz\contentfield\json\scope\State;
use lenz\contentfield\models\fields\ArrayField;
use lenz\contentfield\models\values\ArrayValue;
use lenz\contentfield\models\values\InstanceValue;
use Throwable;

/**
 * Class AnchorProperty
 */
class AnchorProperty extends AbstractProperty
{
  public $definitionType = 'contentfield.AnchorDefinition|undefined';


  /**
   * @inheritDoc
   */
  public function export(object $target, $source, State $state) {
    if (!($source instanceof InstanceValue)) {
      return;
    }

    if ($source->hasAnchor()) {
      $target->{$this->name} = [
        'id' => $source->getAnchor(),
        'title' => $source->getAnchorTitle(),
      ];
    }
  }
}
