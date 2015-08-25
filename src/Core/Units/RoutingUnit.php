<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;
use Jivoo\Controllers\ActionDispatcher;
use Jivoo\Controllers\Controllers;
use Jivoo\Core\LoadableModule;
use Jivoo\Snippets\SnippetDispatcher;
use Jivoo\Snippets\Snippets;
use Jivoo\Routing\Routing;

/**
 * Initializes the routing module.
 */
class RoutingUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  protected $requires = array('Request');
  
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $app->m->routing = new Routing($app, false);
    
    $app->m->routing->dispatchers->add(
      new ActionDispatcher($app)
    );

    $app->m->routing->dispatchers->add(
      new SnippetDispatcher($app)
    );
    
    $this->m->routing->loadRoutes();

    $app->on('ready', array($app->m->routing, 'findRoute'));
  }
}