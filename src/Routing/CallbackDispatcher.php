<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * Callback based routing.
 */
class CallbackDispatcher implements Dispatcher {
  /**
   * {@inheritdoc}
   */
  public function getPrefixes() {
    return array('callback');
  }

  /**
   * {@inheritdoc}
   */
  public function validate(&$route) {
    return isset($route['callback']);
  }
  
  /**
   * {@inheritdoc}
   */
  public function autoRoute(RoutingTable $table, $route, $resource = false) {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function toRoute($routeString) {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function fromRoute($route) {
    return '';
  }
  
  /**
   * {@inheritdoc}
   */
  public function isCurrent($route) {
    return false;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPath($route, $path = null) {
    return $route['path'];
  }

  /**
   * {@inheritdoc}
   */
  public function createDispatch($route) {
    return $route['callback'];
  }
}
