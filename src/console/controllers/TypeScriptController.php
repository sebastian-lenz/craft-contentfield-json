<?php

namespace lenz\contentfield\json\console\controllers;

use craft\console\Controller;
use lenz\contentfield\json\Plugin;

/**
 * TypeScript utilities.
 */
class TypeScriptController extends Controller
{
  /**
   * Generate a TypeScript definition file.
   *
   * @param string $fileName The filename of the definition file.
   */
  public function actionIndex(string $fileName = './types.d.ts') {
    $definitions = Plugin::getInstance()->project->toDefinitions();

    $dirName = dirname($fileName);
    if (!file_exists($dirName)) {
      mkdir($dirName, 0777, true);
    }

    if (file_exists($fileName)) {
      unlink($fileName);
    }

    file_put_contents($fileName, $definitions);
  }
}
