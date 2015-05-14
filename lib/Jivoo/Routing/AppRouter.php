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
  protected $handlers = array(
    'Jivoo\Routing\Routing.beforeLoadRoutes',
    'Jivoo\Routing\Routing.afterLoadRoutes'
  );

  /**
   * @var bool Whether or not to load routes before the application routes.
   */
  protected $beforeConfig = false;
  
  /**
   *
   */
  protected $priority = 7;
  
  /**
   * 
   */
  public function beforeLoadRoutes() {
    if ($this->beforeConfig) {
      $this->createRoutes($this->m->Routing->routes);
      $current = $this->m->Routing->route;
      if (is_array($current) and isset($current['priority'])) {
        if ($current['priority'] > $this->priority)
          return;
      }
      if ($this->checkPath($this->request->path)) {
        
      }
    }
  }

  /**
   *
   */
  public function afterLoadRoutes() {
    if (!$this->beforeConfig) {
      $this->createRoutes($this->m->Routing->routes);
      $current = $this->m->Routing->route;
      if (is_array($current) and isset($current['priority'])) {
        if ($current['priority'] > $this->priority)
          return;
      }
      if ($this->checkPath($this->request->path)) {
        
      }
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
  protected function reroute($route, $arity, $priority = null) {
    if (!isset($priority))
      $priority = $this->priority;
    $this->m->Routing->addPathFunction($route, array($this, 'getPath'), $arity, $priority);
  }

  /**
   *
   */
  protected function setRoute($route, $priority = null) {
    if (!isset($priority))
      $priority = $this->priority;
    $this->m->Routing->setRoute($route, $priority);
  }
}