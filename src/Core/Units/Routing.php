<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;

/**
 * Initializes the routing module.
 */
class Routing extends UnitBase {
  /**
   * {@inheritdoc}
   */
  protected $requires = array('Request');
  
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $this->logger->debug('run unit: routing');
//     $app->m->routing = new \Jivoo\Routing\Routing($app);
  }
}