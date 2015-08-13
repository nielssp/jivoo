<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * A dummy dispatcher that handles 'void:' routes.
 */
class VoidDispatcher implements IDispatcher {
  /**
   * {@inheritdoc}
   */
  public function getPrefixes() {
    return array('void');
  }

  /**
   * {@inheritdoc}
   */
  public function validate(&$route) {
    return isset($route['void']);
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
    return array('void' => true);
  }

  /**
   * {@inheritdoc}
   */
  public function fromRoute($route) {
    return 'void:';
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
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function createDispatch($route) {
    throw new InvalidRouteException(tr('Trying to dispatch void route.'));
  }
}