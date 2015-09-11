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
use Jivoo\Core\LoadableModule;
use Jivoo\Setup\Setup;

/**
 * Initializes the cache system.
 */
class SetupUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  protected $requires = array('Routing', 'AppLogic', 'Session');

  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $app->m->Setup = new Setup($app);
    $this->m->units->one('unitDone', function() use($app) {
      $app->m->Setup->runInit();
    });
  }
}