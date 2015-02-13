<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * A collection of dispatcher objects.
 */
class DispatcherCollection {
  /**
   * @var Routing Routing module.
   */
  private $routing;
  
  /**
   * @var IDispatcher[] Dispatchers.
   */
  private $dispatchers = array();
  
  /**
   * Construct collection of dispatcher objects.
   * @param Routing $routing Routing module.
   */
  public function __construct(Routing $routing) {
    $this->routing = $routing;
  }
  
  /**
   * Add a dispatcher object.
   * @param IDispatcher $dispatcher Dispatcher object.
   */
  public function add(IDispatcher $dispatcher) {
    $prefixes = $dispatcher->getPrefixes();
    foreach ($prefixes as $prefix) {
      $this->dispatchers[$prefix] = $dispatcher;
    }
  }
  
  /**
   * Validate a route.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @throws InvalidRouteException If route is not valid.
   * @return array A valid route array.
   */
  public function validate($route) {
    if (!isset($route)) {
      $route = array('path' => array(), 'query' => array(), 'fragment' => null);
    }
    else if (is_string($route)) {
      $route = $this->toRoute($route);
    }
    else if (is_object($route) and $route instanceof ILinkable) {
      $route = $route->getRoute();
    }
    if (!is_array($route))
      throw new InvalidRouteException(tr('Not a valid route, must be array or string'));
    if (!isset($route['query']))
      $route['query'] = array();
    if (isset($route['mergeQuery']) and $route['mergeQuery'] == true)
      $route['query'] = array_merge($this->request->query, $route['query']);
    if (!isset($route['fragment']))
      $route['fragment'] = null;
    if (isset($route['dispatcher']))
      return $route;
    foreach ($this->dispatchers as $dispatcher) {
      if ($dispatcher->validate($route)) {
        $route['dispatcher'] = $dispatcher;
        break;
      }
    }
    if (!isset($route['dispatcher']))
      throw new InvalidRouteException(tr('No dispatcher found for route'));
    return $route;
  }
  
  /**
   * Convert a route string to a route array.
   * @param string $routeString Route string.
   * @throws InvalidRouteException If string has unknown prefix.
   * @return array A route array.
   */
  public function toRoute($routeString) {
    if (preg_match('/^([^:]+):/', $routeString, $matches) === 1) {
      $prefix = $matches[1];
      if (isset($this->dispatchers[$prefix])) {
        $route = $this->dispatchers[$prefix]->toRoute($routeString);
        $route['dispatcher'] = $this->dispatchers[$prefix];
        return $this->validate($route);
      }
    }
    throw new InvalidRouteException(tr('Unknown route prefix.'));
  }
  
}