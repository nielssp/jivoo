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
   * @var string[] Last pattern
   */
  private $pattern = null;
  
  /**
   * @var string[] Nested patterns.
   */
  private $nestStack = array();

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
    return $this;
  }

  /**
   * Automatically create routes for all actions in a controller or just a
   * single action.
   * @param array|Linkable|string|null $route A route, see {@see Routing}.
   * @param array $options An associative array of options for auto routing.
   * @return self Self.
   */
  public function auto($route, $options = array()) {
    $route = $this->routing->validateRoute($route);
    $pattern = $route['dispatcher']->autoRoute($this, $route, false);
    if (!isset($pattern))
      throw new InvalidRouteException(tr('Auto routing not possible for route.'));
    $this->pattern = $pattern;
    return $this;
  }
  
  /**
   * Create route for root, i.e. the frontpage.
   * @param array|Linkable|string|null $route A route, {@see Routing}.
   * @return self Self.
   */
  public function root($route) {
    $this->routing->setRoot($route);
    return $this;
  }
  
  /**
   * Create route for error page.
   * @param array|Linkable|string|null $route A route, {@see Routing}.
   * @return self Self.
   */
  public function error($route) {
    $this->routing->setError($route);
    return $this;
  }


  /**
   * Create route for requests matching a pattern.
   * @param string $pattern A path to match, see {@see Routing::addRoute}.
   * @param array|Linkable|string|null $route A route, {@see Routing}.
   * @param int $priority Priority of route.
   * @return self Self.
   */
  public function match($pattern, $route, $priority = 5) {
    if (isset($this->nestStack[0]) and $this->nestStack[0] !== '') {
      $pattern = $this->nestStack[0] . '/' . self::incrementParameters($pattern);
    }
    $this->routing->addRoute($pattern, $route, $priority);
    return $this;
  }
  
  /**
   * Automatically create routes for a resource. Expects controller to be set in
   * the route.
   * @param array|Linkable|string|null $route A route, {@see Routing}.
   * @return self Self.
   */
  public function resource($route) {
    $route = $this->routing->validateRoute($route);
    $pattern = $route['dispatcher']->autoRoute($this, $route, true);
    if (!isset($pattern))
      throw new InvalidRouteException(tr('Auto routing not possible for route.'));
    $this->pattern = $pattern;
    return $this;
  }
  
  /**
   * Nest resources.
   * @return self Self.
   */
  public function nest() {
    if (isset($this->nestStack[0]) and $this->nestStack[0] !== '') {
      $this->pattern = $this->nestStack[0] . '/' . self::incrementParameters($this->pattern);
    }
    array_unshift($this->nestStack, $this->pattern);
    return $this;
  }
  
  /**
   * End nesting of resources.
   * @return self Self.
   */
  public function end() {
    array_shift($this->nestStack);
    return $this;
  }
  
  /**
   * Increment numeric parameter-patterns.
   * @param string $pattern Pattern.
   * @return string Pattern.
   */
  public static function incrementParameters($pattern) {
    return preg_replace_callback(
      '/:(\d+)/',
      function($matches) {
        return ':' . ($matches[1] + 1);
      },
      $pattern
    );
  }
}
