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

/**
 * Initializes application logic such as controllers, helpers, models, etc.
 */
class AppLogicUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    Enum::addSearchPrefix($app->n('Enums') . '\\');
    $app->m->helpers = new ModuleLoader();
    $app->m->helpers->setLoader(array($this, 'loadHelper'));
  }
  
  public function loadHelper($name) {
    $class = $this->app->n('Helpers\\' . $name . 'Helper');
    if (!class_exists($class)) {
      if (strpos($name, '\\') !== false) {
        $class = $name . 'Helper';
        $name = Utilities::getClassName($name);
      }
      else {
        $class = 'Jivoo\Helpers\\' . $name . 'Helper';
      }
    }
    $this->triggerEvent('beforeLoadHelper', new LoadHelperEvent($this, $name));
    Utilities::assumeSubclassOf($class, 'Jivoo\Helpers\Helper');
    $this->helpers[$name] = new $class($this->app);
    $this->triggerEvent('afterLoadHelper', new LoadHelperEvent($this, $name, $this->helpers[$name]));
  }
}