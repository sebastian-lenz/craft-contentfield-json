<?php

namespace lenz\contentfield\json\events;

use lenz\contentfield\json\Plugin;
use yii\base\Event;

/**
 * Class RegisterHtmlFiltersEvent
 */
class RegisterFiltersEvent extends Event
{
  /**
   * @var callable[]
   */
  public $filters = [];


  // Static methods
  // --------------

  /**
   * @param string $html
   * @return string
   */
  static public function applyAliases(string $html): string {
    return preg_replace_callback('/href="([^"]*)"/', function($match) {
      return 'href="' . Plugin::toAlias($match[1]) . '"';
    }, $html);
  }
}
