<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

use Jivoo\Core\AppListener;

/**
 * 
 */
class AppDispatcher extends AppListener implements IDispatcher {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Routing');

  /**
   * {@inheritdoc}
   */
  protected $handlers = array('Jivoo\Routing\Routing.beforeLoadRoutes');

  private $route = null;
  private $priority = 7;
  
  /**
   * 
   */
  public function beforeLoadRoutes() {
    $this->m->Routing->dispatchers->add($this);
  }
  
  protected function setRoute($route, $priority = 7) {
    $this->route = $route;
    $this->priority = $priority;
  }
  
  public function checkPath($path) {
    
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPrefixes() {
    return array(
      get_class($this)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(&$route) {
    if (isset($route['AppDispatcher']) and $route['AppDispatcher'] === get_class($this)) {
      return true;
    }
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function autoRoute(RoutingTable $table, $route, $resource = false) {
    if ($this->checkPath($this->request->path)) {
      $this->m->Routing->setRoute($this->route, $this->priority);
      return implode('/', $this->request->path);
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function toRoute($routeString) {
    $key = preg_replace('/^[^:]+:/', '', $routeString);
    return array('AppDispatcher' => get_class($this));
  }

  /**
   * {@inheritdoc}
   */
  public function fromRoute($route) {
    return get_class($this) . ':';
  }

  /**
   * {@inheritdoc}
   */
  public function isCurrent($route) {
    
  }

  /**
   * {@inheritdoc}
   */
  public function getPath($route, $path = null) {
    
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch($route) {
    
  }
}