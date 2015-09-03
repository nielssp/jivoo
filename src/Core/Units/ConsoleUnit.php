<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;
use Jivoo\Core\LoadableModule;
use Jivoo\Console\Console;

/**
 * Initializes the developper's console system.
 */
class ConsoleUnit extends UnitBase {  
  /**
   * {@inheritdoc}
   */
  protected $requires = array('AppLogic', 'Routing', 'Extensions');
  
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $this->m->Console = new Console($app);
    $this->m->Console->runInit();
  }
}