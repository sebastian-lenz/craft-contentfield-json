<?php

namespace lenz\contentfield\json\events;

use lenz\contentfield\json\helpers\AliasHelper;
use yii\base\Event;

/**
 * Class AliasEvent
 */
class AliasEvent extends Event
{
  /**
   * @var AliasHelper
   */
  public $aliases;
}
