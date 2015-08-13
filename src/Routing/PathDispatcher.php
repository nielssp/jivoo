<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * Path-array based routing.
 */
class PathDispatcher implements Dispatcher {
  /**
   * @var Routing Routing module.
   */
  private $routing;
  
  /**
   * Construct url dispatcher.
   * @param Routing $routing Routing module.
   */
  public function __construct(Routing $routing) {
    $this->routing = $routing;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPrefixes() {
    return array('path');
  }

  /**
   * {@inheritdoc}
   */
  public function validate(&$route) {
    return isset($route['path']);
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
    return array('path' => explode('/', substr($routeString, 5)));
  }

  /**
   * {@inheritdoc}
   */
  public function fromRoute($route) {
    return 'path:' . implode('/', $route['path']);
  }
  
  /**
   * {@inheritdoc}
   */
  public function isCurrent($route) {
    $request = $this->routing->request;
    $root = $this->routing->root;
    if ($route['path'] == array()) {
      if ($request->path == array()) {
        return true;
      }
      if (isset($root) and isset($root['path'])
        and $request->path == $root['path']) {
        return true;
      }
    }
    return $request->path == $route['path'];
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
    return function() use($route) {
      return $this->routing->redirectPath($route['path']);
    };
  }
}