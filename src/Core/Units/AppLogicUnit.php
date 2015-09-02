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
use Jivoo\Core\Assume;

/**
 * Initializes application logic such as controllers, helpers, models, etc.
 */
class AppLogicUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  protected $requires = array('State', 'Vendor', 'Cache');
  
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    Enum::addSearchPrefix($app->n('Enums') . '\\');
    $app->m->Helpers = new Helpers($app);
    $app->m->Helpers->addHelper('Jivoo\Snippets\SnippetHelper');
    $app->m->Helpers->runInit();
    $app->m->Helpers->addHelper('Jivoo\AccessControl\AuthHelper');
    $app->m->addMethod('helper', array($app->m->Helpers, 'getHelper'));
    $app->m->Models = new Models($app);
    $app->m->Models->runInit();

    $listeners = $this->p('app/Listeners');
    if (is_dir($listeners)) {
      $files = scandir($listeners);
      if ($files !== false) {
        foreach ($files as $file) {
          $split = explode('.', $file);
          if (isset($split[1]) and $split[1] == 'php') {
            $listener = $this->app->n('Listeners\\' . $split[0]);
            Assume::isSubclassOf($listener, 'Jivoo\Core\AppListener');
            $this->app->attachEventListener(new $listener($this->app));
          }
        }
      }
    }
  }
}