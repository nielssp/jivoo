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
  
  protected $prefix;
  
  /**
   * 
   */
  public function beforeLoadRoutes() {
    if (!isset($this->prefix))
      $this->prefix = Lib::getClassName($this);
    $this->m->Routing->dispatchers->add($this);
  }
  
  protected function setRoute($route, $priority = 7) {
    $route['AppDispatcher'] = get_class($this);
    $this->route = $route;
    $this->priority = $priority;
  }
  
  public function checkPath($path) {
    return false;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPrefixes() {
    return array(
      $this->prefix
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
    return $this->prefix . ':';
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
  
  public function transform($rotue) {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch($route) {
    $route = $this->m->Routing->dispatchers->validate($this->transform($route));
    return $route['dispatcher']->dispatch($route);
  }
}