<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

use Jivoo\Core\AppListener;
use Jivoo\Core\Lib;

/**
 * 
 */
abstract class AppRouter extends AppListener {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Routing');

  /**
   * {@inheritdoc}
   */
  protected $handlers = array('Jivoo\Routing\Routing.beforeLoadRoutes');

  /**
   *
   */
  private $route = null;

  /**
   *
   */
  private $priority = 7;
  
  /**
   * 
   */
  public function beforeLoadRoutes() {
    $this->createRoutes($this->m->Routing->routes);
    if ($this->checkPath($this->request->path)) {
      
    }
  }

  /**
   *
   */
  protected abstract function createRoutes(RoutingTable $routes);

  /**
   *
   */
  public function checkPath($path) {
    return false;
  }

  /**
   * 
   */
  public function getPath($route, $path = null) {
    
  }

  /**
   *
   */
  protected function reroute($route, $arity, $priority = 7) {
    $this->m->Routing->addPathFunction($route, array($this, 'getPath'), $arity, $priority);
  }

  /**
   *
   */
  protected function setRoute($route, $priority = 7) {
    $this->m->Routing->setRoute($route, $priority);
  }
}