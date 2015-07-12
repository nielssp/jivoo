<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * A dummy dispatcher that handles 'null:' routes.
 */
class NulLDispatcher implements IDispatcher {
  /**
   * {@inheritdoc}
   */
  public function getPrefixes() {
    return array('null');
  }

  /**
   * {@inheritdoc}
   */
  public function validate(&$route) {
    return isset($route['null']);
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
    return array('null' => true);
  }

  /**
   * {@inheritdoc}
   */
  public function fromRoute($route) {
    return 'null:';
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
    throw new InvalidRouteException(tr('Trying to dispatch null route.'));
  }
}