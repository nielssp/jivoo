<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;
use Jivoo\Core\Cache\Cache;
use Jivoo\Models\Enum;
use Jivoo\Core\ModuleLoader;
use Jivoo\Core\LoadableModule;
use Jivoo\Helpers\Helpers;
use Jivoo\Models\Models;

/**
 * Initializes old modules.
 */
class LegacyModulesUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  protected $requires = array('Request');
  
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $modules = $this->app->manifest['modules'];
    $modules = array_intersect($modules, array(
      'AccessControl', 'Console', 'Content', 'Extensions', 'Jtk', 'Themes'
    ));
    foreach ($modules as $module) {
      $class = 'Jivoo\\' . $module . '\\' . $module;
      $this->m->$module = new $class($app);
      $this->m->$module->runInit();
    }
  }
}