<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * Used for configuring routes and paths.
 */
class RoutingTable {
  /**
   * @var Routing Routing module.
   */
  private $routing;

  /**
   * Construct routing table.
   * @param Routing $routing Routing module.
   */
  public function __construct(Routing $routing) {
    $this->routing = $routing;
  }

  /**
   * Load routing file.
   * @param string $file File.
   * @return self Self.
   */
  public function load($file) {
    require $file;
  }

  /**
   * Automatically create routes for all actions in a controller or just a
   * single action.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param array $options An associative array of options for auto routing.
   * @return self Self.
   */
  public function auto($route, $options = array()) {
    return $this;
  }
  
  /**
   * Create route for root, i.e. the frontpage.
   * @param array|ILinkable|string|null $route A route, {@see Routing}.
   * @return self Self.
   */
  public function root($route) {
    $this->routing->setRoot($route);
    return $htis;
  }
  
  /**
   * Create route for error page.
   * @param array|ILinkable|string|null $route A route, {@see Routing}.
   * @return self Self.
   */
  public function error($route) {
    $this->routing->setError($route);
    return $this;
  }


  /**
   * Create route for requests matching a pattern.
   * @param string $pattern A path to match, see {@see Routing::addRoute}.
   * @param array|ILinkable|string|null $route A route, {@see Routing}.
   * @param int $priority Priority of route.
   * @return self Self.
   */
  public function match($pattern, $route, $priority = 5) {
    $this->routing->addRoute($pattern, $route, $priority);
    return $this;
  }
  
  /**
   * Automatically create routes for a resource. Expects controller to be set in
   * the route.
   * @param array|ILinkable|string|null $route A route, {@see Routing}.
   * @return self Self.
   */
  public function resource($route) {
    return $this;
  }
}
